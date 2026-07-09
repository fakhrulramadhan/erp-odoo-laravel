<?php

namespace Database\Seeders;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Models\SupplierQuotation;
use App\Models\SupplierQuotationLine;
use App\Models\StockPicking;
use App\Models\StockMove;
use App\Models\InventoryAdjustment;
use App\Models\InventoryAdjustmentLine;
use App\Models\StockQuant;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Warehouse;
use App\Models\StockLocation;
use App\Models\Branch;
use App\Models\TaxSetting;
use App\Models\UnitOfMeasure;
use App\Enums\PurchaseOrderStatus;
use App\Enums\PickingType;
use App\Enums\PickingStatus;
use App\Enums\StockMoveStatus;
use App\Enums\QuotationStatus;
use Illuminate\Database\Seeder;

class PurchaseInventorySeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        $currency = Currency::where('code', 'IDR')->first();
        $branch = Branch::first();
        $warehouse = Warehouse::first();
        $vendor = Vendor::first();
        $products = Product::limit(5)->get();
        $uom = UnitOfMeasure::first();
        $tax = TaxSetting::first();
        $user = \App\Models\User::first();

        if (!$company || $products->isEmpty() || !$vendor) {
            $this->command->warn('Skipping PurchaseInventorySeeder — missing company, products, or vendor.');
            return;
        }

        $locations = StockLocation::limit(3)->get();
        if ($locations->isEmpty()) {
            $location = StockLocation::create([
                'name' => 'Main Storage',
                'code' => 'MAIN',
                'warehouse_id' => $warehouse->id ?? 1,
                'is_active' => true,
            ]);
            $locations = collect([$location]);
        }

        // ─── Purchase Orders ─────────────────────────
        foreach ([PurchaseOrderStatus::Draft, PurchaseOrderStatus::Approved, PurchaseOrderStatus::Ordered, PurchaseOrderStatus::Received] as $status) {
            $po = PurchaseOrder::create([
                'order_number' => 'PO-' . str_pad(random_int(1000, 9999), 6, '0', STR_PAD_LEFT),
                'vendor_id' => $vendor->id,
                'company_id' => $company->id,
                'currency_id' => $currency->id,
                'warehouse_id' => $warehouse->id,
                'branch_id' => $branch->id,
                'status' => $status,
                'order_date' => now()->subDays(rand(1, 30)),
                'expected_date' => now()->addDays(rand(7, 60)),
                'notes' => 'Sample PO for ' . $status->value,
                'created_by' => $user->id,
            ]);

            foreach ($products->take(3) as $i => $product) {
                $qty = rand(10, 100);
                $price = rand(50000, 500000);
                $total = $qty * $price;

                PurchaseOrderLine::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $product->id,
                    'uom_id' => $uom->id,
                    'tax_id' => $tax?->id,
                    'line_number' => $i + 1,
                    'description' => $product->name,
                    'quantity' => $qty,
                    'price' => $price,
                    'tax_rate' => 11,
                    'tax_amount' => $total * 0.11,
                    'subtotal' => $total,
                    'total' => $total * 1.11,
                    'delivery_date' => now()->addDays(rand(7, 30)),
                ]);
            }

            $po->recalculate();
        }

        // ─── Supplier Quotation ─────────────────────
        $sq = SupplierQuotation::create([
            'quotation_number' => 'RFQ-000001',
            'vendor_id' => $vendor->id,
            'company_id' => $company->id,
            'currency_id' => $currency->id,
            'status' => QuotationStatus::Sent,
            'quotation_date' => now()->subDays(5),
            'validity_date' => now()->addDays(30),
            'notes' => 'Sample supplier quotation',
            'created_by' => $user->id,
        ]);

        foreach ($products->take(2) as $i => $product) {
            $qty = rand(5, 50);
            $price = rand(50000, 300000);

            SupplierQuotationLine::create([
                'supplier_quotation_id' => $sq->id,
                'product_id' => $product->id,
                'uom_id' => $uom->id,
                'line_number' => $i + 1,
                'quantity' => $qty,
                'price' => $price,
                'subtotal' => $qty * $price,
                'total' => $qty * $price,
            ]);
        }
        $sq->recalculate();

        // ─── Stock Pickings ─────────────────────────
        $picking = StockPicking::create([
            'picking_number' => 'IN-000001',
            'company_id' => $company->id,
            'picking_type' => PickingType::Incoming,
            'status' => PickingStatus::Done,
            'source_location_id' => $locations->first()->id,
            'destination_location_id' => $locations->first()->id,
            'vendor_id' => $vendor->id,
            'scheduled_date' => now()->subDays(3),
            'effective_date' => now()->subDays(2),
            'notes' => 'Sample incoming picking',
            'created_by' => $user->id,
        ]);

        foreach ($products->take(3) as $i => $product) {
            $qty = rand(20, 80);
            StockMove::create([
                'stock_picking_id' => $picking->id,
                'product_id' => $product->id,
                'uom_id' => $uom->id,
                'move_number' => 'SM-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'status' => StockMoveStatus::Done,
                'source_location_id' => $locations->first()->id,
                'destination_location_id' => $locations->first()->id,
                'quantity' => $qty,
                'done_qty' => $qty,
                'unit_cost' => rand(50000, 200000),
                'effective_date' => now()->subDays(2),
                'created_by' => $user->id,
            ]);
        }

        // ─── Stock Quants ───────────────────────────
        foreach ($products as $product) {
            $qty = rand(50, 500);
            StockQuant::create([
                'product_id' => $product->id,
                'location_id' => $locations->first()->id,
                'quantity' => $qty,
                'reserved_quantity' => rand(0, (int)($qty * 0.2)),
                'available_quantity' => $qty - rand(0, (int)($qty * 0.2)),
                'incoming_quantity' => rand(0, 100),
                'outgoing_quantity' => rand(0, 30),
                'unit_cost' => rand(50000, 200000),
                'total_value' => $qty * rand(50000, 200000),
                'last_updated_at' => now(),
            ]);
        }

        // ─── Inventory Adjustment ───────────────────
        $adj = InventoryAdjustment::create([
            'adjustment_number' => 'ADJ-000001',
            'company_id' => $company->id,
            'warehouse_id' => $warehouse->id,
            'status' => 'draft',
            'adjustment_date' => now(),
            'reason' => 'Cycle count adjustment',
            'notes' => 'Sample inventory adjustment',
            'created_by' => $user->id,
        ]);

        foreach ($products->take(2) as $product) {
            $theoretical = rand(50, 100);
            $actual = $theoretical + rand(-10, 10);

            InventoryAdjustmentLine::create([
                'inventory_adjustment_id' => $adj->id,
                'product_id' => $product->id,
                'location_id' => $locations->first()->id,
                'theoretical_qty' => $theoretical,
                'actual_qty' => $actual,
                'difference' => $actual - $theoretical,
            ]);
        }

        $this->command->info('✅ Purchase & Inventory sample data seeded.');
    }
}
