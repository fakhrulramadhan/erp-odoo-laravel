<?php

namespace App\Services\Sales;

use App\Enums\FinanceStatus;
use App\Enums\PickingStatus;
use App\Enums\PickingType;
use App\Enums\SalesOrderStatus;
use App\Enums\StockMoveStatus;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\Journal;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use App\Models\StockLocation;
use App\Models\StockPicking;
use App\Models\Warehouse;
use App\Repositories\StockPickingRepository;
use App\Services\Finance\FinanceService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalesOrderService
{
    public function __construct(
        protected FinanceService $financeService,
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        $query = SalesOrder::with(['customer', 'currency', 'warehouse', 'creator'])
            ->when(!empty($filters['search']), function ($q) use ($filters) {
                $search = $filters['search'];
                $q->where(function ($sub) use ($search) {
                    $sub->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"));
                });
            })
            ->when(!empty($filters['status']), fn ($q) => $q->where('status', $filters['status']))
            ->when(!empty($filters['customer_id']), fn ($q) => $q->where('customer_id', $filters['customer_id']))
            ->when(!empty($filters['date_from']), fn ($q) => $q->whereDate('order_date', '>=', $filters['date_from']))
            ->when(!empty($filters['date_to']), fn ($q) => $q->whereDate('order_date', '<=', $filters['date_to']))
            ->orderByDesc('id');

        return $query->paginate($perPage);
    }

    public function find(int $id): SalesOrder
    {
        return SalesOrder::with([
            'customer', 'company', 'currency', 'warehouse',
            'lines.product', 'lines.uom', 'confirmer', 'creator', 'invoice',
        ])->findOrFail($id);
    }

    public function create(array $data): SalesOrder
    {
        return DB::transaction(function () use ($data) {
            $lines = $data['lines'] ?? [];
            unset($data['lines']);

            $data['order_number'] = $this->getNextOrderNumber();
            $data['status'] = SalesOrderStatus::Draft;
            $data['created_by'] = Auth::id();
            $data['company_id'] ??= Auth::user()?->company_id
                ?? \App\Models\Company::query()->orderBy('id')->value('id');
            $data['warehouse_id'] ??= Warehouse::query()->where('is_main', true)->value('id')
                ?? Warehouse::query()->orderBy('id')->value('id');

            $order = SalesOrder::create($data);
            $this->writeLines($order, $lines);
            $order->recalculate();

            return $this->find($order->id);
        });
    }

    public function update(int $id, array $data): SalesOrder
    {
        return DB::transaction(function () use ($id, $data) {
            $order = SalesOrder::findOrFail($id);

            if ($order->status !== SalesOrderStatus::Draft) {
                throw new \DomainException('Only draft sales orders can be edited.');
            }

            $lines = $data['lines'] ?? null;
            unset($data['lines'], $data['status'], $data['order_number']);

            $data['updated_by'] = Auth::id();
            $order->update($data);

            if ($lines !== null) {
                $order->lines()->delete();
                $this->writeLines($order, $lines);
                $order->recalculate();
            }

            return $this->find($id);
        });
    }

    protected function writeLines(SalesOrder $order, array $lines): void
    {
        foreach ($lines as $index => $line) {
            $fields = $this->normalizeLine($line);
            $fields['line_number'] = $index + 1;

            $model = new SalesOrderLine($fields);
            $model->calculateTotals();

            $order->lines()->create($model->getAttributes());
        }
    }

    /**
     * Accept the same field names the purchasing UI uses.
     */
    protected function normalizeLine(array $line): array
    {
        $taxRate = (float) ($line['tax_rate'] ?? 0);
        if ($taxRate === 0 && !empty($line['tax_id'])) {
            $taxRate = (float) (\App\Models\TaxSetting::whereKey($line['tax_id'])->value('rate') ?? 0);
        }

        return [
            'product_id' => $line['product_id'],
            'uom_id' => $line['uom_id'] ?? null,
            'tax_id' => $line['tax_id'] ?? null,
            'description' => $line['description'] ?? null,
            'quantity' => (float) ($line['quantity'] ?? 0),
            'unit_price' => (float) ($line['unit_price'] ?? $line['price'] ?? 0),
            'discount_percent' => (float) ($line['discount_rate'] ?? $line['discount_percent'] ?? 0),
            'tax_rate' => $taxRate,
            'delivery_date' => $line['delivery_date'] ?? null,
        ];
    }

    public function confirm(int $id): SalesOrder
    {
        return DB::transaction(function () use ($id) {
            $order = SalesOrder::findOrFail($id);
            $order->transitionTo(SalesOrderStatus::Confirmed);
            $order->update(['confirmed_by' => Auth::id(), 'confirmed_at' => now()]);

            return $this->find($id);
        });
    }

    public function cancel(int $id): SalesOrder
    {
        return DB::transaction(function () use ($id) {
            $order = SalesOrder::findOrFail($id);
            $order->transitionTo(SalesOrderStatus::Cancelled);
            $order->update(['cancelled_by' => Auth::id(), 'cancelled_at' => now()]);

            return $this->find($id);
        });
    }

    /**
     * Create the outgoing picking that ships the ordered goods.
     */
    public function createDelivery(int $id): SalesOrder
    {
        return DB::transaction(function () use ($id) {
            $order = $this->find($id);

            if (!in_array($order->status, [SalesOrderStatus::Confirmed, SalesOrderStatus::PartiallyDelivered])) {
                throw new \DomainException('Only confirmed or partially delivered sales orders can be shipped.');
            }

            $active = $order->pickings()
                ->whereNotIn('status', [PickingStatus::Cancelled->value, PickingStatus::Done->value])
                ->first();
            if ($active) {
                throw new \DomainException('An open delivery already exists for this sales order.');
            }

            $sourceLocation = StockLocation::query()
                ->where('type', 'internal')
                ->when($order->warehouse_id, fn ($q) => $q->where('warehouse_id', $order->warehouse_id))
                ->orderBy('id')
                ->first()
                ?? StockLocation::where('type', 'internal')->orderBy('id')->first();

            if (!$sourceLocation) {
                throw new \DomainException('No internal stock location found. Create one first.');
            }

            $picking = StockPicking::create([
                'picking_number' => app(StockPickingRepository::class)->getNextPickingNumber(PickingType::Outgoing->value),
                'company_id' => $order->company_id,
                'picking_type' => PickingType::Outgoing,
                'status' => PickingStatus::Draft,
                'origin' => 'sale:' . $order->id,
                'source_location_id' => $sourceLocation->id,
                'destination_location_id' => null,
                'customer_id' => $order->customer_id,
                'sales_order_id' => $order->id,
                'scheduled_date' => $order->delivery_date ?? now()->toDateString(),
                'notes' => 'Delivery for ' . $order->order_number,
                'created_by' => Auth::id(),
            ]);

            foreach ($order->lines as $line) {
                $remaining = $line->getRemainingQty();
                if ($remaining <= 0) {
                    continue;
                }

                $picking->moves()->create([
                    'product_id' => $line->product_id,
                    'uom_id' => $line->uom_id,
                    'source_location_id' => $sourceLocation->id,
                    'destination_location_id' => null,
                    'quantity' => $remaining,
                    'done_qty' => 0,
                    'unit_cost' => 0,
                    'total_cost' => 0,
                    'origin' => 'so-line:' . $line->id,
                    'status' => StockMoveStatus::Draft,
                    'created_by' => Auth::id(),
                ]);
            }

            return $this->find($id);
        });
    }

    /**
     * Follow the shipped quantities once a delivery is validated.
     */
    public function syncDeliveredQuantities(int $id): void
    {
        $order = SalesOrder::with('lines')->find($id);

        if (!$order || !in_array($order->status, [SalesOrderStatus::Confirmed, SalesOrderStatus::PartiallyDelivered])) {
            return;
        }

        $deliveredByProduct = [];
        $pickings = $order->pickings()->where('status', PickingStatus::Done->value)->with('moves')->get();

        foreach ($pickings as $picking) {
            foreach ($picking->moves as $move) {
                $deliveredByProduct[$move->product_id] = ($deliveredByProduct[$move->product_id] ?? 0) + (float) $move->done_qty;
            }
        }

        foreach ($order->lines as $line) {
            $line->update([
                'delivered_qty' => min((float) $line->quantity, $deliveredByProduct[$line->product_id] ?? 0),
            ]);
        }

        $order->load('lines');
        $fullyDelivered = $order->lines->every(fn ($l) => (float) $l->delivered_qty >= (float) $l->quantity);
        $anyDelivered = $order->lines->sum('delivered_qty') > 0;

        if ($fullyDelivered) {
            $order->update(['status' => SalesOrderStatus::Delivered, 'effective_date' => now()->toDateString()]);
        } elseif ($anyDelivered) {
            $order->update(['status' => SalesOrderStatus::PartiallyDelivered]);
        }
    }

    /**
     * Customer invoice: Dr Accounts Receivable / Cr Sales Revenue.
     */
    public function createInvoice(int $id): Invoice
    {
        return DB::transaction(function () use ($id) {
            $order = $this->find($id);

            if (!in_array($order->status, [SalesOrderStatus::Delivered, SalesOrderStatus::PartiallyDelivered])) {
                throw new \DomainException('Only delivered or partially delivered sales orders can be invoiced.');
            }

            $existing = Invoice::where('source_type', 'sales_order')
                ->where('source_id', $order->id)
                ->where('invoice_type', 'customer')
                ->first();

            if ($existing && $existing->status !== FinanceStatus::Draft) {
                return $existing;
            }

            if ($existing) {
                $existing->items()->delete();
                $invoice = $existing;
            } else {
                $invoice = Invoice::create([
                    'company_id' => $order->company_id,
                    'invoice_number' => $this->financeService->generateNumber('INV'),
                    'invoice_type' => 'customer',
                    'customer_id' => $order->customer_id,
                    'currency_id' => $order->currency_id,
                    'invoice_date' => now()->toDateString(),
                    'status' => FinanceStatus::Draft,
                    'source_type' => 'sales_order',
                    'source_id' => $order->id,
                    'notes' => 'Customer invoice for ' . $order->order_number,
                    'created_by' => Auth::id(),
                ]);
            }

            $subtotal = 0;
            foreach ($order->lines as $line) {
                $qty = (float) $line->delivered_qty;
                if ($qty <= 0) {
                    continue;
                }

                $lineTotal = round($qty * (float) $line->unit_price, 2);
                $subtotal += $lineTotal;

                $invoice->items()->create([
                    'product_id' => $line->product_id,
                    'description' => $line->description ?? 'SO Line #' . $line->line_number,
                    'quantity' => $qty,
                    'unit_price' => (float) $line->unit_price,
                    'discount_rate' => 0,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'line_total' => $lineTotal,
                ]);
            }

            if ($subtotal <= 0) {
                throw new \DomainException('Nothing has been delivered yet, so there is nothing to invoice.');
            }

            $invoice->update([
                'subtotal' => round($subtotal, 2),
                'total_amount' => round($subtotal, 2),
                'balance_amount' => round($subtotal - (float) $invoice->paid_amount, 2),
            ]);

            $invoice = $this->financeService->postInvoice($invoice->id);
            // postInvoice skips the journal for sales-order invoices, so post it here.
            $this->postRevenueJournal($order, $invoice);

            $order->update(['status' => SalesOrderStatus::Invoiced]);

            return $invoice->fresh()->load('items');
        });
    }

    protected function postRevenueJournal(SalesOrder $order, Invoice $invoice): void
    {
        $amount = round((float) $invoice->total_amount, 2);
        if ($amount <= 0) {
            return;
        }

        $journal = Journal::where('code', 'MJ')->first() ?? Journal::first();
        $receivable = Account::where('code', '1130')->first();
        $revenue = Account::where('code', '4010')->first();

        if (!$journal || !$receivable || !$revenue) {
            Log::warning('Sales invoice journal skipped: missing journal or 1130/4010 account.');
            return;
        }

        // Skip if postInvoice already booked this reference.
        $alreadyPosted = \App\Models\JournalEntry::where('reference', $order->order_number)->exists();
        if ($alreadyPosted) {
            return;
        }

        $this->financeService->createJournalEntry([
            'journal_id' => $journal->id,
            'entry_date' => now()->toDateString(),
            'description' => "Sales invoice {$invoice->invoice_number} for {$order->order_number}",
            'reference' => $order->order_number,
            'lines' => [
                ['account_id' => $receivable->id, 'debit_amount' => $amount, 'credit_amount' => 0, 'description' => 'Accounts Receivable'],
                ['account_id' => $revenue->id, 'debit_amount' => 0, 'credit_amount' => $amount, 'description' => 'Sales Revenue'],
            ],
        ]);
    }

    protected function getNextOrderNumber(): string
    {
        $last = SalesOrder::withTrashed()->orderBy('id', 'desc')->first();

        if (!$last) {
            return 'SO-000001';
        }

        $next = (int) preg_replace('/\D/', '', substr($last->order_number, 3)) + 1;

        return 'SO-' . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }
}
