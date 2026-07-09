<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Finance\StoreInvoiceRequest;
use App\Http\Requests\Api\V1\Finance\StoreJournalEntryRequest;
use App\Http\Requests\Api\V1\Finance\StorePaymentRequest;
use App\Models\Account;
use App\Models\CashAccount;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Payment;
use App\Models\Tax;
use App\Services\Finance\FinanceService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    use ApiResponse;

    public function __construct(protected FinanceService $service) {}

    public function dashboard(): JsonResponse
    {
        return $this->success([
            'receivable' => Invoice::where('invoice_type', 'customer')->whereIn('status', ['posted', 'paid'])->sum('balance_amount'),
            'payable' => Invoice::where('invoice_type', 'vendor')->whereIn('status', ['posted', 'paid'])->sum('balance_amount'),
            'cash_balance' => CashAccount::sum('current_balance'),
            'bank_balance' => 0,
            'revenue_this_month' => Invoice::where('invoice_type', 'customer')->whereMonth('invoice_date', now()->month)->sum('total_amount'),
            'expense_this_month' => Invoice::where('invoice_type', 'vendor')->whereMonth('invoice_date', now()->month)->sum('total_amount'),
        ]);
    }

    public function accounts(Request $request): JsonResponse
    {
        $filters = $request->only(['search']);
        return $this->success($this->service->listAccounts($filters));
    }

    public function journals(): JsonResponse
    {
        return $this->success(\App\Models\Journal::all());
    }

    public function journalEntries(Request $request): JsonResponse
    {
        $entries = JournalEntry::with('lines.account', 'journal')->latest()->paginate($request->input('per_page', 15));
        return $this->paginated($entries);
    }

    public function storeJournalEntry(StoreJournalEntryRequest $request): JsonResponse
    {
        $entry = $this->service->createJournalEntry($request->validated());
        return $this->created($entry, 'Journal entry created successfully.');
    }

    public function postJournalEntry(int $id): JsonResponse
    {
        $entry = $this->service->postJournalEntry($id);
        return $this->success($entry, 'Journal entry posted successfully.');
    }

    public function invoices(Request $request): JsonResponse
    {
        $invoices = Invoice::with('items', 'customer', 'vendor', 'currency')->latest()->paginate($request->input('per_page', 15));
        return $this->paginated($invoices);
    }

    public function storeInvoice(StoreInvoiceRequest $request): JsonResponse
    {
        $invoice = $this->service->createInvoice($request->validated());
        return $this->created($invoice, 'Invoice created successfully.');
    }

    public function postInvoice(int $id): JsonResponse
    {
        $invoice = $this->service->postInvoice($id);
        return $this->success($invoice, 'Invoice posted successfully.');
    }

    public function payments(Request $request): JsonResponse
    {
        $payments = Payment::with('allocations', 'customer', 'vendor')->latest()->paginate($request->input('per_page', 15));
        return $this->paginated($payments);
    }

    public function storePayment(StorePaymentRequest $request): JsonResponse
    {
        $payment = $this->service->createPayment($request->validated());
        return $this->created($payment, 'Payment created successfully.');
    }

    public function postPayment(int $id): JsonResponse
    {
        $payment = $this->service->postPayment($id);
        return $this->success($payment, 'Payment posted successfully.');
    }

    public function taxes(): JsonResponse
    {
        return $this->success(Tax::query()->where('is_active', true)->get());
    }
}
