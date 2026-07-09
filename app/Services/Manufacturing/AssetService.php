<?php

namespace App\Services\Manufacturing;

use App\Enums\AssetStatus;
use App\Enums\DepreciationMethod;
use App\Models\Asset;
use App\Repositories\AssetRepository;
use App\Services\Finance\FinanceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class AssetService
{
    public function __construct(
        protected AssetRepository $repository,
        protected FinanceService $financeService,
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): Asset
    {
        return $this->repository->findWithRelations($id);
    }

    public function create(array $data): Asset
    {
        return DB::transaction(function () use ($data) {
            $data['asset_number'] = $data['asset_number'] ?? $this->repository->getNextAssetNumber();
            $data['status'] = AssetStatus::Draft;
            $data['accumulated_depreciation'] = 0;
            $data['book_value'] = $data['acquisition_cost'] ?? 0;
            $data['created_by'] = auth()->id();

            $asset = $this->repository->create($data);
            return $this->repository->findWithRelations($asset->id);
        });
    }

    public function update(int $id, array $data): Asset
    {
        return DB::transaction(function () use ($id, $data) {
            $asset = $this->repository->findById($id);

            if (!in_array($asset->status, [AssetStatus::Draft])) {
                throw new \DomainException('Cannot edit an asset unless it is in draft status.');
            }

            $data['updated_by'] = auth()->id();
            $this->repository->update($id, $data);

            // Recalculate book value
            $asset->refresh();
            $asset->update(['book_value' => $asset->getBookValue()]);

            return $this->repository->findWithRelations($id);
        });
    }

    public function activate(int $id): Asset
    {
        return DB::transaction(function () use ($id) {
            $asset = $this->repository->findById($id);
            $asset->transitionTo(AssetStatus::Active);
            $asset->update([
                'start_depreciation_date' => now(),
                'updated_by' => auth()->id(),
            ]);
            return $this->repository->findWithRelations($id);
        });
    }

    public function transfer(int $id, array $data): \App\Models\AssetTransfer
    {
        return DB::transaction(function () use ($id, $data) {
            $asset = $this->repository->findById($id);

            if ($asset->status !== AssetStatus::Active) {
                throw new \DomainException('Only active assets can be transferred.');
            }

            $transfer = $asset->transfers()->create([
                'transfer_date' => $data['transfer_date'] ?? now()->toDateString(),
                'from_branch_id' => $asset->branch_id,
                'from_department_id' => $asset->department_id,
                'to_branch_id' => $data['to_branch_id'],
                'to_department_id' => $data['to_department_id'],
                'to_location' => $data['to_location'] ?? null,
                'to_custodian_id' => $data['to_custodian_id'] ?? null,
                'reason' => $data['reason'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $asset->update([
                'branch_id' => $data['to_branch_id'],
                'department_id' => $data['to_department_id'] ?? $asset->department_id,
                'location' => $data['to_location'] ?? $asset->location,
                'custodian_id' => $data['to_custodian_id'] ?? $asset->custodian_id,
                'status' => AssetStatus::Transferred,
            ]);

            return $transfer;
        });
    }

    public function dispose(int $id, array $data = []): Asset
    {
        return DB::transaction(function () use ($id, $data) {
            $asset = $this->repository->findWithRelations($id);

            if ($asset->status !== AssetStatus::Active && $asset->status !== AssetStatus::Transferred) {
                throw new \DomainException('Only active or transferred assets can be disposed.');
            }

            $asset->transitionTo(AssetStatus::Disposed);
            $asset->update([
                'disposal_date' => now()->toDateString(),
                'disposal_amount' => $data['disposal_amount'] ?? 0,
                'disposal_reason' => $data['disposal_reason'] ?? null,
                'updated_by' => auth()->id(),
            ]);

            // Create disposal journal
            $this->createDisposalJournal($asset, (float) ($data['disposal_amount'] ?? 0));

            return $this->repository->findWithRelations($id);
        });
    }

    public function depreciate(int $id): Asset
    {
        return DB::transaction(function () use ($id) {
            $asset = $this->repository->findWithRelations($id);

            if ($asset->status !== AssetStatus::Active) {
                throw new \DomainException('Only active assets can be depreciated.');
            }

            $monthlyDepreciation = $asset->getMonthlyDepreciation();
            $remainingBookValue = $asset->getBookValue();

            if ($monthlyDepreciation <= 0 || $remainingBookValue <= 0) {
                throw new \DomainException('No depreciation amount remaining for this asset.');
            }

            $depreciationAmount = min($monthlyDepreciation, $remainingBookValue);

            $depreciation = $asset->depreciations()->create([
                'depreciation_date' => now()->toDateString(),
                'period' => now()->format('Y-m'),
                'method' => $asset->depreciation_method,
                'amount' => $depreciationAmount,
                'accumulated_before' => (float) $asset->accumulated_depreciation,
                'accumulated_after' => (float) $asset->accumulated_depreciation + $depreciationAmount,
                'book_value_before' => $remainingBookValue,
                'book_value_after' => $remainingBookValue - $depreciationAmount,
                'created_by' => auth()->id(),
            ]);

            $asset->update([
                'accumulated_depreciation' => (float) $asset->accumulated_depreciation + $depreciationAmount,
                'book_value' => $remainingBookValue - $depreciationAmount,
            ]);

            // Create depreciation journal
            $this->createDepreciationJournal($asset, $depreciationAmount);

            return $this->repository->findWithRelations($id);
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $asset = $this->repository->findById($id);
            if ($asset->status !== AssetStatus::Draft) {
                throw new \DomainException('Cannot delete an asset unless it is in draft status.');
            }
            return $this->repository->delete($id);
        });
    }

    protected function createDepreciationJournal(Asset $asset, float $amount): void
    {
        if ($amount <= 0) return;

        try {
            $category = $asset->category;
            if (!$category) return;

            $journal = \App\Models\Journal::where('code', 'MJ')->first()
                ?? \App\Models\Journal::first();
            if (!$journal) return;

            $depreciationAccount = \App\Models\Account::find($category->depreciation_expense_account_id);
            $accumulatedAccount = \App\Models\Account::find($category->accumulated_depreciation_account_id);

            if (!$depreciationAccount || !$accumulatedAccount) return;

            $entry = $this->financeService->createJournalEntry([
                'journal_id' => $journal->id,
                'entry_date' => now()->toDateString(),
                'description' => "Depreciation: {$asset->asset_number} - {$asset->name}",
                'reference' => $asset->asset_number,
                'lines' => [
                    [
                        'account_id' => $depreciationAccount->id,
                        'description' => 'Depreciation Expense',
                        'debit_amount' => $amount,
                        'credit_amount' => 0,
                    ],
                    [
                        'account_id' => $accumulatedAccount->id,
                        'description' => 'Accumulated Depreciation',
                        'debit_amount' => 0,
                        'credit_amount' => $amount,
                    ],
                ],
            ]);

            // Link journal entry to depreciation
            $latestDepreciation = $asset->depreciations()->latest()->first();
            if ($latestDepreciation && isset($entry->id)) {
                $latestDepreciation->update(['journal_entry_id' => $entry->id]);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to create depreciation journal: ' . $e->getMessage());
        }
    }

    protected function createDisposalJournal(Asset $asset, float $disposalAmount): void
    {
        try {
            $category = $asset->category;
            if (!$category) return;

            $journal = \App\Models\Journal::where('code', 'MJ')->first()
                ?? \App\Models\Journal::first();
            if (!$journal) return;

            $assetAccount = \App\Models\Account::find($category->asset_account_id);
            $accumulatedAccount = \App\Models\Account::find($category->accumulated_depreciation_account_id);
            $disposalAccount = \App\Models\Account::find($category->disposal_account_id);
            $cashAccount = \App\Models\Account::where('code', '1000')->first();

            if (!$assetAccount || !$accumulatedAccount) return;

            $lines = [];

            // Credit: Asset cost
            $lines[] = [
                'account_id' => $assetAccount->id,
                'description' => 'Asset Disposal - ' . $asset->name,
                'debit_amount' => 0,
                'credit_amount' => (float) $asset->acquisition_cost,
            ];

            // Debit: Accumulated Depreciation
            $lines[] = [
                'account_id' => $accumulatedAccount->id,
                'description' => 'Accumulated Depreciation - ' . $asset->asset_number,
                'debit_amount' => (float) $asset->accumulated_depreciation,
                'credit_amount' => 0,
            ];

            // Debit: Cash received (if any)
            if ($disposalAmount > 0 && $cashAccount) {
                $lines[] = [
                    'account_id' => $cashAccount->id,
                    'description' => 'Disposal Proceeds',
                    'debit_amount' => $disposalAmount,
                    'credit_amount' => 0,
                ];
            }

            // Balance: Gain/Loss on disposal
            $bookValue = $asset->getBookValue();
            $gainLoss = $disposalAmount - $bookValue;
            if (abs($gainLoss) > 0.01) {
                $gainLossAccount = $disposalAccount ?? \App\Models\Account::where('code', '8000')->first();
                if ($gainLossAccount) {
                    if ($gainLoss > 0) {
                        $lines[] = [
                            'account_id' => $gainLossAccount->id,
                            'description' => 'Gain on Disposal',
                            'debit_amount' => 0,
                            'credit_amount' => abs($gainLoss),
                        ];
                    } else {
                        $lines[] = [
                            'account_id' => $gainLossAccount->id,
                            'description' => 'Loss on Disposal',
                            'debit_amount' => abs($gainLoss),
                            'credit_amount' => 0,
                        ];
                    }
                }
            }

            $this->financeService->createJournalEntry([
                'journal_id' => $journal->id,
                'entry_date' => now()->toDateString(),
                'description' => "Asset Disposal: {$asset->asset_number} - {$asset->name}",
                'reference' => $asset->asset_number,
                'lines' => $lines,
            ]);
        } catch (\Exception $e) {
            \Log::warning('Failed to create disposal journal: ' . $e->getMessage());
        }
    }
}
