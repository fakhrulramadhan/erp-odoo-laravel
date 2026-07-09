<?php

namespace App\Services\Finance;

use App\Enums\FinanceStatus;
use App\Models\Account;
use App\Models\CashAccount;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\PaymentTerm;
use App\Models\Tax;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FinanceService
{
    public function listAccounts(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = Account::query()->with('parent');

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('code', 'like', "%{$filters['search']}%" );
            });
        }

        return $query->orderBy('code')->get();
    }

    public function createJournalEntry(array $data): JournalEntry
    {
        return DB::transaction(function () use ($data) {
            $entry = JournalEntry::create([
                'company_id' => Auth::user()?->company_id ?? 1,
                'journal_id' => $data['journal_id'],
                'entry_number' => $this->generateNumber('JE'),
                'reference' => $data['reference'] ?? null,
                'entry_date' => $data['entry_date'],
                'description' => $data['description'] ?? null,
                'status' => FinanceStatus::Draft,
                'created_by' => Auth::id(),
            ]);

            $totalDebit = 0;
            $totalCredit = 0;

            foreach ($data['lines'] as $line) {
                $debit = (float) ($line['debit_amount'] ?? 0);
                $credit = (float) ($line['credit_amount'] ?? 0);
                $totalDebit += $debit;
                $totalCredit += $credit;

                $entry->lines()->create([
                    'account_id' => $line['account_id'],
                    'description' => $line['description'] ?? null,
                    'debit_amount' => $debit,
                    'credit_amount' => $credit,
                ]);
            }

            if (round($totalDebit, 2) !== round($totalCredit, 2)) {
                throw new \DomainException('Debit and credit must be balanced.');
            }

            return $entry->load('lines.account');
        });
    }

    public function postJournalEntry(int $id): JournalEntry
    {
        return DB::transaction(function () use ($id) {
            $entry = JournalEntry::findOrFail($id);

            if ($entry->status !== FinanceStatus::Draft) {
                throw new \DomainException('Only draft journal entries can be posted.');
            }

            $totalDebit = $entry->lines->sum('debit_amount');
            $totalCredit = $entry->lines->sum('credit_amount');
            if (round($totalDebit, 2) !== round($totalCredit, 2)) {
                throw new \DomainException('Journal entry is not balanced.');
            }

            $entry->update([
                'status' => FinanceStatus::Posted,
                'posted_by' => Auth::id(),
                'posted_at' => now(),
            ]);

            return $entry->fresh()->load('lines.account');
        });
    }

    public function createInvoice(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            $invoice = Invoice::create([
                'company_id' => Auth::user()?->company_id ?? 1,
                'invoice_number' => $this->generateNumber('INV'),
                'invoice_type' => $data['invoice_type'],
                'customer_id' => $data['customer_id'] ?? null,
                'vendor_id' => $data['vendor_id'] ?? null,
                'currency_id' => $data['currency_id'],
                'payment_term_id' => $data['payment_term_id'] ?? null,
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'] ?? null,
                'status' => FinanceStatus::Draft,
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $quantity = (float) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];
                $discountRate = (float) ($item['discount_rate'] ?? 0);
                $discountAmount = round($quantity * $unitPrice * $discountRate / 100, 2);
                $taxAmount = 0;
                $lineTotal = round(($quantity * $unitPrice) - $discountAmount + $taxAmount, 2);

                $subtotal += $lineTotal;

                $invoice->items()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'tax_id' => $item['tax_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_rate' => $discountRate,
                    'discount_amount' => $discountAmount,
                    'tax_amount' => $taxAmount,
                    'line_total' => $lineTotal,
                ]);
            }

            $invoice->update([
                'subtotal' => round($subtotal, 2),
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => round($subtotal, 2),
                'balance_amount' => round($subtotal, 2),
            ]);

            if (!empty($data['payment_term_id'])) {
                $term = PaymentTerm::find($data['payment_term_id']);
                if ($term) {
                    $invoice->update(['due_date' => $invoice->invoice_date->addDays($term->days)]);
                }
            }

            return $invoice->fresh()->load('items');
        });
    }

    public function postInvoice(int $id): Invoice
    {
        return DB::transaction(function () use ($id) {
            $invoice = Invoice::findOrFail($id);
            if ($invoice->status !== FinanceStatus::Draft) {
                throw new \DomainException('Only draft invoices can be posted.');
            }

            $invoice->update([
                'status' => FinanceStatus::Posted,
                'posted_by' => Auth::id(),
                'posted_at' => now(),
            ]);

            return $invoice->fresh()->load('items');
        });
    }

    public function createPayment(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $payment = Payment::create([
                'company_id' => Auth::user()?->company_id ?? 1,
                'payment_number' => $this->generateNumber('PMT'),
                'payment_type' => $data['payment_type'],
                'customer_id' => $data['customer_id'] ?? null,
                'vendor_id' => $data['vendor_id'] ?? null,
                'currency_id' => $data['currency_id'],
                'payment_method' => $data['payment_method'],
                'payment_method_id' => $data['payment_method_id'] ?? null,
                'bank_account_id' => $data['bank_account_id'] ?? null,
                'cash_account_id' => $data['cash_account_id'] ?? null,
                'payment_date' => $data['payment_date'],
                'status' => FinanceStatus::Draft,
                'amount' => $data['amount'],
                'allocated_amount' => 0,
                'balance_amount' => $data['amount'],
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $allocated = 0;
            foreach ($data['allocations'] ?? [] as $allocation) {
                $amount = (float) $allocation['amount'];
                $payment->allocations()->create([
                    'invoice_id' => $allocation['invoice_id'],
                    'amount' => $amount,
                ]);
                $allocated += $amount;
            }

            $payment->update([
                'allocated_amount' => $allocated,
                'balance_amount' => round((float) $data['amount'] - $allocated, 2),
            ]);

            return $payment->fresh()->load('allocations');
        });
    }

    public function postPayment(int $id): Payment
    {
        return DB::transaction(function () use ($id) {
            $payment = Payment::findOrFail($id);
            if ($payment->status !== FinanceStatus::Draft) {
                throw new \DomainException('Only draft payments can be posted.');
            }

            $payment->update([
                'status' => FinanceStatus::Posted,
                'posted_by' => Auth::id(),
                'posted_at' => now(),
            ]);

            if ($payment->allocations->isNotEmpty()) {
                foreach ($payment->allocations as $allocation) {
                    $invoice = Invoice::findOrFail($allocation->invoice_id);
                    $invoice->paid_amount = round((float) $invoice->paid_amount + (float) $allocation->amount, 2);
                    $invoice->balance_amount = round((float) $invoice->total_amount - (float) $invoice->paid_amount, 2);
                    if ($invoice->balance_amount <= 0) {
                        $invoice->status = FinanceStatus::Paid;
                    }
                    $invoice->save();
                }
            }

            return $payment->fresh()->load('allocations');
        });
    }

    protected function generateNumber(string $prefix): string
    {
        return $prefix . '-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
    }
}
