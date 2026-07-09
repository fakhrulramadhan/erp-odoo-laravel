<?php

namespace App\Services\Manufacturing;

use App\Models\ScrapOrder;
use App\Models\StockQuant;
use App\Repositories\ScrapOrderRepository;
use App\Services\Finance\FinanceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class ScrapOrderService
{
    public function __construct(
        protected ScrapOrderRepository $repository,
        protected FinanceService $financeService,
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): ScrapOrder
    {
        return $this->repository->findWithRelations($id);
    }

    public function create(array $data): ScrapOrder
    {
        return DB::transaction(function () use ($data) {
            $data['scrap_number'] = $this->repository->getNextScrapNumber();
            $data['status'] = 'draft';
            $data['created_by'] = auth()->id();

            // Get unit cost from stock quant
            if (empty($data['unit_cost']) && !empty($data['product_id']) && !empty($data['location_id'])) {
                $quant = StockQuant::where('product_id', $data['product_id'])
                    ->where('location_id', $data['location_id'])
                    ->first();
                $data['unit_cost'] = $quant?->unit_cost ?? 0;
            }

            $data['total_cost'] = round((float) $data['quantity'] * (float) ($data['unit_cost'] ?? 0), 2);

            $scrap = $this->repository->create($data);
            return $this->repository->findWithRelations($scrap->id);
        });
    }

    public function confirm(int $id): ScrapOrder
    {
        return DB::transaction(function () use ($id) {
            $scrap = $this->repository->findById($id);

            if ($scrap->status !== 'draft') {
                throw new \DomainException('Only draft scrap orders can be confirmed.');
            }

            $scrap->update(['status' => 'confirmed']);
            return $this->repository->findWithRelations($id);
        });
    }

    public function process(int $id): ScrapOrder
    {
        return DB::transaction(function () use ($id) {
            $scrap = $this->repository->findWithRelations($id);

            if ($scrap->status !== 'confirmed') {
                throw new \DomainException('Only confirmed scrap orders can be processed.');
            }

            // Reduce inventory
            if ($scrap->location_id) {
                $quant = StockQuant::firstOrCreate(
                    ['product_id' => $scrap->product_id, 'location_id' => $scrap->location_id],
                    ['quantity' => 0, 'reserved_quantity' => 0, 'available_quantity' => 0, 'unit_cost' => 0]
                );
                $quant->removeQuantity((float) $scrap->quantity);
            }

            // Create accounting journal for scrap
            $this->createScrapJournal($scrap);

            $scrap->update(['status' => 'done']);
            return $this->repository->findWithRelations($id);
        });
    }

    public function cancel(int $id): ScrapOrder
    {
        return DB::transaction(function () use ($id) {
            $scrap = $this->repository->findById($id);
            $scrap->update(['status' => 'cancelled']);
            return $this->repository->findWithRelations($id);
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $scrap = $this->repository->findById($id);
            if ($scrap->status !== 'draft') {
                throw new \DomainException('Cannot delete a scrap order that is not in draft status.');
            }
            return $this->repository->delete($id);
        });
    }

    protected function createScrapJournal(ScrapOrder $scrap): void
    {
        if ($scrap->total_cost <= 0) return;

        try {
            $journal = \App\Models\Journal::where('code', 'MJ')->first()
                ?? \App\Models\Journal::first();
            if (!$journal) return;

            $scrapExpenseAccount = \App\Models\Account::where('code', '5200')->first(); // Scrap Expense
            $inventoryAccount = \App\Models\Account::where('code', '1200')->first(); // Inventory

            if (!$scrapExpenseAccount || !$inventoryAccount) return;

            $this->financeService->createJournalEntry([
                'journal_id' => $journal->id,
                'entry_date' => now()->toDateString(),
                'description' => "Scrap: {$scrap->scrap_number} - {$scrap->reason}",
                'reference' => $scrap->scrap_number,
                'lines' => [
                    [
                        'account_id' => $scrapExpenseAccount->id,
                        'description' => 'Scrap Expense - ' . ($scrap->product?->name ?? ''),
                        'debit_amount' => $scrap->total_cost,
                        'credit_amount' => 0,
                    ],
                    [
                        'account_id' => $inventoryAccount->id,
                        'description' => 'Inventory Reduction - ' . $scrap->scrap_number,
                        'debit_amount' => 0,
                        'credit_amount' => $scrap->total_cost,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            \Log::warning('Failed to create scrap journal: ' . $e->getMessage());
        }
    }
}
