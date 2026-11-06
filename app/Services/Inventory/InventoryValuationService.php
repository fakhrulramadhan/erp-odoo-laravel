<?php

namespace App\Services\Inventory;

use App\Models\Account;
use App\Models\Journal;
use App\Services\Finance\FinanceService;
use Illuminate\Support\Facades\Log;

/**
 * Central place for posting inventory valuation journals.
 *
 * Account codes used here are seeded by FinanceSeeder:
 *   1030 Inventory          5020 Inventory Adjustment
 *   2110 Accounts Payable   5030 Scrap Expense
 *   2120 GRNI               5010 Cost of Goods Sold
 *   1300 Work In Progress
 */
class InventoryValuationService
{
    public function __construct(
        protected FinanceService $financeService
    ) {}

    /**
     * Goods received against a purchase order.
     * Debit Inventory / Credit GRNI (the vendor invoice settles GRNI later).
     */
    public function recordGoodsReceipt(string $reference, float $amount, string $description): void
    {
        if ($amount <= 0) {
            return;
        }

        $this->post(
            $reference,
            $description,
            [
                ['code' => '1030', 'debit' => $amount, 'credit' => 0, 'label' => 'Inventory'],
                ['code' => '2120', 'debit' => 0, 'credit' => $amount, 'label' => 'Goods Received Not Invoiced'],
            ]
        );
    }

    /**
     * Goods leaving the warehouse (delivery, internal consumption).
     * Debit COGS / Credit Inventory.
     */
    public function recordGoodsIssue(string $reference, float $amount, string $description): void
    {
        if ($amount <= 0) {
            return;
        }

        $this->post(
            $reference,
            $description,
            [
                ['code' => '5010', 'debit' => $amount, 'credit' => 0, 'label' => 'Cost of Goods Sold'],
                ['code' => '1030', 'debit' => 0, 'credit' => $amount, 'label' => 'Inventory'],
            ]
        );
    }

    /**
     * Stock adjustment: positive difference is a gain, negative a loss.
     */
    public function recordAdjustment(string $reference, float $amount, string $description): void
    {
        if (abs($amount) < 0.01) {
            return;
        }

        $gain = $amount > 0;

        $this->post(
            $reference,
            $description,
            [
                [
                    'code' => $gain ? '1030' : '5020',
                    'debit' => abs($amount),
                    'credit' => 0,
                    'label' => $gain ? 'Inventory' : 'Inventory Adjustment',
                ],
                [
                    'code' => $gain ? '5020' : '1030',
                    'debit' => 0,
                    'credit' => abs($amount),
                    'label' => $gain ? 'Inventory Adjustment' : 'Inventory',
                ],
            ]
        );
    }

    /**
     * Scrapped stock: Debit Scrap Expense / Credit Inventory.
     */
    public function recordScrap(string $reference, float $amount, string $description): void
    {
        if ($amount <= 0) {
            return;
        }

        $this->post(
            $reference,
            $description,
            [
                ['code' => '5030', 'debit' => $amount, 'credit' => 0, 'label' => 'Scrap Expense'],
                ['code' => '1030', 'debit' => 0, 'credit' => $amount, 'label' => 'Inventory'],
            ]
        );
    }

    /**
     * Production completion: Debit Inventory (finished goods) / Credit WIP.
     */
    public function recordProduction(string $reference, float $amount, string $description): void
    {
        if ($amount <= 0) {
            return;
        }

        $this->post(
            $reference,
            $description,
            [
                ['code' => '1030', 'debit' => $amount, 'credit' => 0, 'label' => 'Finished Goods'],
                ['code' => '1300', 'debit' => 0, 'credit' => $amount, 'label' => 'Work In Progress'],
            ]
        );
    }

    /**
     * Raw materials consumed by production: Debit WIP / Credit Inventory.
     */
    public function recordMaterialConsumption(string $reference, float $amount, string $description): void
    {
        if ($amount <= 0) {
            return;
        }

        $this->post(
            $reference,
            $description,
            [
                ['code' => '1300', 'debit' => $amount, 'credit' => 0, 'label' => 'Work In Progress'],
                ['code' => '1030', 'debit' => 0, 'credit' => $amount, 'label' => 'Inventory'],
            ]
        );
    }

    /**
     * Vendor invoice posted: Debit GRNI / Credit Accounts Payable.
     */
    public function recordVendorInvoice(string $reference, float $amount, string $description): void
    {
        if ($amount <= 0) {
            return;
        }

        $this->post(
            $reference,
            $description,
            [
                ['code' => '2120', 'debit' => $amount, 'credit' => 0, 'label' => 'Goods Received Not Invoiced'],
                ['code' => '2110', 'debit' => 0, 'credit' => $amount, 'label' => 'Accounts Payable'],
            ]
        );
    }

    /**
     * Resolve the given account codes to ids and post the journal.
     * Missing accounts are logged and skipped so a half-configured chart of
     * accounts never breaks the operational flow.
     */
    protected function post(string $reference, string $description, array $lines): void
    {
        try {
            $journal = Journal::where('code', 'MJ')->first() ?? Journal::first();
            if (!$journal) {
                return;
            }

            $resolved = [];
            foreach ($lines as $line) {
                $account = Account::where('code', $line['code'])->first();
                if (!$account) {
                    Log::warning("Inventory journal skipped: account {$line['code']} not found.");
                    return;
                }

                $resolved[] = [
                    'account_id' => $account->id,
                    'description' => $line['label'] . ' - ' . $reference,
                    'debit_amount' => round($line['debit'], 2),
                    'credit_amount' => round($line['credit'], 2),
                ];
            }

            $this->financeService->createJournalEntry([
                'journal_id' => $journal->id,
                'entry_date' => now()->toDateString(),
                'description' => $description,
                'reference' => $reference,
                'lines' => $resolved,
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to post inventory journal: ' . $e->getMessage());
        }
    }
}
