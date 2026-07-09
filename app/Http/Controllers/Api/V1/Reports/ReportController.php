<?php

namespace App\Http\Controllers\Api\V1\Reports;

use App\Http\Controllers\Controller;
use App\Models\{PurchaseOrder, PurchaseOrderLine, Vendor, Product, StockQuant, StockPicking, Warehouse};
use App\Traits\ApiResponse;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\{DB, Schema};

class ReportController extends Controller
{
    use ApiResponse;

    /**
     * Purchasing reports summary.
     * ?type=by_status|by_vendor|by_month|top_products
     */
    public function purchasing(Request $request): JsonResponse
    {
        $type = $request->query('type', 'summary');

        return match ($type) {
            'by_vendor'   => $this->purchasingByVendor($request),
            'by_month'    => $this->purchasingByMonth($request),
            'top_products' => $this->purchasingTopProducts($request),
            default       => $this->purchasingSummary($request),
        };
    }

    /**
     * Inventory reports summary.
     * ?type=by_warehouse|low_stock|stock_value
     */
    public function inventory(Request $request): JsonResponse
    {
        $type = $request->query('type', 'summary');

        return match ($type) {
            'by_warehouse' => $this->inventoryByWarehouse($request),
            'low_stock'    => $this->inventoryLowStock($request),
            'stock_value'  => $this->inventoryStockValue($request),
            default        => $this->inventorySummary($request),
        };
    }

    // ─── Purchasing Reports ────────────────────────

    private function purchasingSummary(Request $request): JsonResponse
    {
        $companyId = $request->user()->company_id;

        // PO by status
        $byStatus = PurchaseOrder::select('status', DB::raw('COUNT(*) as count'), DB::raw('COALESCE(SUM(total), 0) as total_value'))
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->groupBy('status')
            ->get()
            ->map(fn($r) => ['status' => $r->status, 'count' => (int) $r->count, 'total_value' => round($r->total_value, 2)]);

        // Overall totals
        $totals = PurchaseOrder::select(
            DB::raw('COUNT(*) as total_orders'),
            DB::raw('COALESCE(SUM(total), 0) as total_value'),
            DB::raw('COUNT(DISTINCT vendor_id) as unique_vendors')
        )->when($companyId, fn($q) => $q->where('company_id', $companyId))->first();

        // Top 5 vendors
        $topVendors = PurchaseOrder::select('vendor_id', DB::raw('COUNT(*) as orders'), DB::raw('COALESCE(SUM(total), 0) as total_spent'))
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->groupBy('vendor_id')
            ->orderByDesc('total_spent')
            ->limit(5)
            ->with('vendor:id,name')
            ->get()
            ->map(fn($r) => [
                'vendor_id' => $r->vendor_id,
                'vendor_name' => $r->vendor?->name,
                'orders' => (int) $r->orders,
                'total_spent' => round($r->total_spent, 2),
            ]);

        return $this->success([
            'totals' => [
                'total_orders' => (int) $totals->total_orders,
                'total_value' => round($totals->total_value, 2),
                'unique_vendors' => (int) $totals->unique_vendors,
            ],
            'by_status' => $byStatus,
            'top_vendors' => $topVendors,
        ]);
    }

    private function purchasingByVendor(Request $request): JsonResponse
    {
        $companyId = $request->user()->company_id;

        $data = PurchaseOrder::select(
            'vendor_id',
            DB::raw('COUNT(*) as orders'),
            DB::raw('COALESCE(SUM(total), 0) as total_spent'),
            DB::raw("SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft"),
            DB::raw("SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved"),
            DB::raw("SUM(CASE WHEN status IN ('ordered','partial_received','received') THEN 1 ELSE 0 END) as active")
        )
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->groupBy('vendor_id')
            ->orderByDesc('total_spent')
            ->with('vendor:id,name')
            ->get()
            ->map(fn($r) => [
                'vendor_id' => $r->vendor_id,
                'vendor_name' => $r->vendor?->name,
                'orders' => (int) $r->orders,
                'total_spent' => round($r->total_spent, 2),
                'draft' => (int) $r->draft,
                'approved' => (int) $r->approved,
                'active' => (int) $r->active,
            ]);

        return $this->success($data);
    }

    private function purchasingByMonth(Request $request): JsonResponse
    {
        $companyId = $request->user()->company_id;
        $year = $request->query('year', date('Y'));

        $data = PurchaseOrder::select(
            DB::raw("strftime('%m', order_date) as month"),
            DB::raw('COUNT(*) as orders'),
            DB::raw('COALESCE(SUM(total), 0) as total_value')
        )
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->whereYear('order_date', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn($r) => [
                'month' => (int) $r->month,
                'month_name' => date('M', mktime(0, 0, 0, (int) $r->month)),
                'orders' => (int) $r->orders,
                'total_value' => round($r->total_value, 2),
            ]);

        return $this->success($data);
    }

    private function purchasingTopProducts(Request $request): JsonResponse
    {
        $data = PurchaseOrderLine::select(
            'product_id',
            DB::raw('SUM(quantity) as total_qty'),
            DB::raw('SUM(total) as total_amount'),
            DB::raw('COUNT(DISTINCT purchase_order_id) as order_count')
        )
            ->groupBy('product_id')
            ->orderByDesc('total_amount')
            ->limit(20)
            ->with('product:id,code,name')
            ->get()
            ->map(fn($r) => [
                'product_id' => $r->product_id,
                'product_code' => $r->product?->code,
                'product_name' => $r->product?->name,
                'total_qty' => round($r->total_qty, 2),
                'total_amount' => round($r->total_amount, 2),
                'order_count' => (int) $r->order_count,
            ]);

        return $this->success($data);
    }

    // ─── Inventory Reports ─────────────────────────

    private function inventorySummary(Request $request): JsonResponse
    {
        // Overall inventory value
        $totalValue = StockQuant::sum('total_value');
        $totalQty = StockQuant::sum('quantity');
        $totalProducts = StockQuant::distinct('product_id')->count('product_id');
        $totalLocations = StockQuant::distinct('location_id')->count('location_id');

        // Low stock products
        $lowStockCount = Product::where('is_active', true)
            ->where('minimum_stock', '>', 0)
            ->get()
            ->filter(fn($p) => StockQuant::where('product_id', $p->id)->sum('quantity') < $p->minimum_stock)
            ->count();

        // Stock by product type
        $byType = Product::select('type', DB::raw('COUNT(*) as count'))
            ->where('is_active', true)
            ->groupBy('type')
            ->get()
            ->map(fn($r) => ['type' => $r->type, 'count' => (int) $r->count]);

        // Top warehouses by value
        $topWarehouses = StockQuant::select('location_id', DB::raw('SUM(total_value) as value'), DB::raw('SUM(quantity) as qty'))
            ->groupBy('location_id')
            ->orderByDesc('value')
            ->limit(5)
            ->with('location:id,name')
            ->get()
            ->map(fn($r) => [
                'location_id' => $r->location_id,
                'location_name' => $r->location?->name,
                'value' => round($r->value, 2),
                'quantity' => round($r->qty, 2),
            ]);

        return $this->success([
            'totals' => [
                'total_value' => round($totalValue, 2),
                'total_quantity' => round($totalQty, 2),
                'total_products' => $totalProducts,
                'total_locations' => $totalLocations,
                'low_stock_count' => $lowStockCount,
            ],
            'by_type' => $byType,
            'top_locations' => $topWarehouses,
        ]);
    }

    private function inventoryByWarehouse(Request $request): JsonResponse
    {
        $data = StockQuant::select(
            'location_id',
            DB::raw('COUNT(DISTINCT product_id) as product_count'),
            DB::raw('SUM(quantity) as total_qty'),
            DB::raw('SUM(reserved_quantity) as reserved_qty'),
            DB::raw('SUM(total_value) as total_value')
        )
            ->groupBy('location_id')
            ->orderByDesc('total_value')
            ->with('location:id,name')
            ->get()
            ->map(fn($r) => [
                'location_id' => $r->location_id,
                'location_name' => $r->location?->name,
                'product_count' => (int) $r->product_count,
                'total_qty' => round($r->total_qty, 2),
                'reserved_qty' => round($r->reserved_qty, 2),
                'total_value' => round($r->total_value, 2),
            ]);

        return $this->success($data);
    }

    private function inventoryLowStock(Request $request): JsonResponse
    {
        $data = Product::where('is_active', true)
            ->where('minimum_stock', '>', 0)
            ->get()
            ->map(function ($product) {
                $onHand = StockQuant::where('product_id', $product->id)->sum('quantity');
                return [
                    'product_id' => $product->id,
                    'code' => $product->code,
                    'name' => $product->name,
                    'on_hand' => round($onHand, 2),
                    'minimum_stock' => $product->minimum_stock,
                    'deficit' => round($product->minimum_stock - $onHand, 2),
                    'cost' => $product->purchase_price,
                    'restock_value' => round(($product->minimum_stock - $onHand) * $product->purchase_price, 2),
                ];
            })
            ->filter(fn($p) => $p['on_hand'] < $p['minimum_stock'])
            ->sortByDesc('deficit')
            ->values();

        return $this->success($data);
    }

    private function inventoryStockValue(Request $request): JsonResponse
    {
        // Stock value grouped by product category
        $data = StockQuant::select(
            'product_id',
            DB::raw('SUM(quantity) as total_qty'),
            DB::raw('SUM(total_value) as total_value')
        )
            ->groupBy('product_id')
            ->orderByDesc('total_value')
            ->with('product:id,code,name,category_id')
            ->with('product.category:id,name')
            ->get()
            ->map(fn($r) => [
                'product_id' => $r->product_id,
                'product_code' => $r->product?->code,
                'product_name' => $r->product?->name,
                'category' => $r->product?->category?->name,
                'quantity' => round($r->total_qty, 2),
                'total_value' => round($r->total_value, 2),
            ]);

        return $this->success($data);
    }
}
