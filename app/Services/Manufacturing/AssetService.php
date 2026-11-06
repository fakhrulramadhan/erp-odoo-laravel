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

    /**
     * The API and UI speak acquisition_*; the table stores purchase_*.
     */
    protected function normalizeInput(array $data): array
    {
        if (array_key_exists('acquisition_cost', $data)) {
            $data['purchase_value'] = $data['acquisition_cost'];
            unset($data['acquisition_cost']);
        }

        if (array_key_exists('acquisition_date', $data)) {
            $data['purchase_date'] = $data['acquisition_date'];
            unset($data['acquisition_date']);
        }

        if (array_key_exists('salvage_value', $data)) {
            $data['residual_value'] = $data['salvage_value'];
            unset($data['salvage_value']);
        }

        if (array_key_exists('asset_category_id', $data)) {
            $data['category_id'] = $data['asset_category_id'];
            unset($data['asset_category_id']);
        }

        // No book_value column: current_value tracks the depreciated amount.
        unset($data['book_value']);

        return $data;
    }

    public function create(array $data): Asset
    {
        return DB::transaction(function () use ($data) {
            $data = $this->normalizeInput($data);
            $data['asset_number'] = $data['asset_number'] ?? $this->repository->getNextAssetNumber();
            $data['status'] = AssetStatus::Draft;
            $data['accumulated_depreciation'] = 0;
            $data['current_value'] = $data['purchase_value'] ?? 0;
            $data['company_id'] ??= auth()->user()?->company_id
                ?? \App\Models\Company::query()->orderBy('id')->value('id');
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

            $data = $this->normalizeInput($data);
            $data['updated_by'] = auth()->id();
            $this->repository->update($id, $data);

            // Keep current_value aligned with accumulated depreciation.
            $asset->refresh();
            $asset->update(['current_value' => $asset->getBookValue()]);

            return $this->repository->findWithRelations($id);
        });
    }

    public function activate(int $id): Asset
    {
        return DB::transaction(function () use ($id) {
            $asset = $this->repository->findById($id);
            $asset->transitionTo(AssetStatus::Active);
            $asset->update([
                'commission_date' => $asset->commission_date ?? now()->toDateString(),
                'updated_by' => auth()->id(),
            ]);
            return $this->repository->findWithRelations($id);
        });
    }

    public function transfer(int $id, array $data): \App\Models\AssetTransfer
    {
        return DB::transaction(function () use ($id, $data) {
            $asset = $this->repository->findById($id);

            if ($asset->status !== AssetStatus::Active && $asset->status !== AssetStatus::Transferred) {
                throw new \DomainException('Only active or transferred assets can be transferred.');
            }

            $transfer = $asset->transfers()->create([
                'transfer_number' => 'AT-' . str_pad((string) (\App\Models\AssetTransfer::max('id') + 1), 6, '0', STR_PAD_LEFT),
                'transfer_date' => $data['transfer_date'] ?? now()->toDateString(),
                'from_location_id' => $asset->location_id,
                'to_location_id' => $data['to_location_id'] ?? null,
                'from_department_id' => $asset->department_id,
                'to_department_id' => $data['to_department_id'] ?? null,
                'from_employee_id' => $asset->assigned_to,
                'to_employee_id' => $data['to_employee_id'] ?? null,
                'reason' => $data['reason'] ?? null,
                'status' => 'done',
                'created_by' => auth()->id(),
            ]);

            $asset->update([
                'location_id' => $data['to_location_id'] ?? $asset->location_id,
                'department_id' => $data['to_department_id'] ?? $asset->department_id,
                'assigned_to' => $data['to_employee_id'] ?? $asset->assigned_to,
                'status' => AssetStatus::Transferred,
                'updated_by' => auth()->id(),
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
                'disposal_value' => $data['disposal_amount'] ?? 0,
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
                'period_number' => $asset->depreciations()->count() + 1,
                'depreciation_amount' => $depreciationAmount,
                'accumulated_depreciation' => (float) $asset->accumulated_depreciation + $depreciationAmount,
                'book_value' => $remainingBookValue - $depreciationAmount,
                'status' => 'posted',
                'created_by' => auth()->id(),
            ]);
            $asset->update([
                'accumulated_depreciation' => (float) $asset->accumulated_depreciation + $depreciationAmount,
                'current_value' => $remainingBookValue - $depreciationAmount,
                'updated_by' => auth()->id(),
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
            $cashAccount = \App\Models\Account::where('code', '1010')->first(); // Cash & Bank

            if (!$assetAccount || !$accumulatedAccount) return;

            $lines = [];

            // Credit: Asset cost
            $lines[] = [
                'account_id' => $assetAccount->id,
                'description' => 'Asset Disposal - ' . $asset->name,
                'debit_amount' => 0,
                'credit_amount' => (float) $asset->purchase_value,
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

            // Guarantee the entry balances even when the gain/loss account is missing
            $debitTotal = array_sum(array_column($lines, 'debit_amount'));
            $creditTotal = array_sum(array_column($lines, 'credit_amount'));
            $difference = round($debitTotal - $creditTotal, 2);

            if (abs($difference) > 0.01) {
                $suspenseAccount = \App\Models\Account::where('code', '8000')->first()
                    ?? \App\Models\Account::where('code', '5020')->first();

                if ($suspenseAccount) {
                    $lines[] = [
                        'account_id' => $suspenseAccount->id,
                        'description' => 'Disposal Balancing Entry',
                        'debit_amount' => $difference > 0 ? 0 : abs($difference),
                        'credit_amount' => $difference > 0 ? $difference : 0,
                    ];
                } else {
                    \Log::warning('Asset disposal journal unbalanced and no suspense account available', [
                        'asset_id' => $asset->id,
                        'difference' => $difference,
                    ]);
                    return;
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
