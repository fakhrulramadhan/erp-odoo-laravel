<?php

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\{
    User, Company, GlobalNotification, AuditTrail,
    PurchaseOrder, SupplierQuotation, PurchaseRequisition,
    StockQuant, StockPicking, Product
};
use App\Services\Audit\AuditService;
use App\Services\Notification\NotificationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AuditService $auditService,
        protected NotificationService $notificationService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $companyId = $request->user()->company_id;

        // ── Phase 1 Stats ──────────────────────────────
        $stats = [
            'total_users' => User::when($companyId, fn($q) => $q->where('company_id', $companyId))->count(),
            'active_users' => User::active()->when($companyId, fn($q) => $q->where('company_id', $companyId))->count(),
            'total_companies' => Company::count(),
            'unread_notifications' => $this->notificationService->getUnreadCount($request->user()),
        ];

        // ── Phase 2: Purchasing Stats ──────────────────
        $poStats = PurchaseOrder::select(
            DB::raw('COUNT(*) as total'),
            DB::raw("SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft"),
            DB::raw("SUM(CASE WHEN status = 'waiting_approval' THEN 1 ELSE 0 END) as waiting_approval"),
            DB::raw("SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved"),
            DB::raw("SUM(CASE WHEN status IN ('ordered','partial_received','received') THEN 1 ELSE 0 END) as active"),
            DB::raw('COALESCE(SUM(total), 0) as total_value')
        )->first();

        $quotationStats = SupplierQuotation::select(
            DB::raw('COUNT(*) as total'),
            DB::raw("SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft"),
            DB::raw("SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent"),
            DB::raw("SUM(CASE WHEN status = 'received' THEN 1 ELSE 0 END) as received"),
            DB::raw("SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted")
        )->first();

        $requisitionStats = PurchaseRequisition::select(
            DB::raw('COUNT(*) as total'),
            DB::raw("SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft"),
            DB::raw("SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) as submitted"),
            DB::raw("SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved")
        )->first();

        // ── Phase 2: Inventory Stats ───────────────────
        $inventoryValue = StockQuant::sum('total_value');
        $totalOnHand = StockQuant::sum('quantity');

        // Low stock: products where total on_hand < minimum_stock
        $lowStockProducts = Product::where('is_active', true)
            ->where('minimum_stock', '>', 0)
            ->select('id', 'code', 'name', 'minimum_stock')
            ->get()
            ->map(function ($product) {
                $onHand = StockQuant::where('product_id', $product->id)->sum('quantity');
                return [
                    'id' => $product->id,
                    'code' => $product->code,
                    'name' => $product->name,
                    'on_hand' => round($onHand, 2),
                    'minimum_stock' => $product->minimum_stock,
                    'deficit' => round($product->minimum_stock - $onHand, 2),
                ];
            })
            ->filter(fn($p) => $p['on_hand'] < $p['minimum_stock'])
            ->values()
            ->take(10);

        $pickingStats = StockPicking::select(
            DB::raw('COUNT(*) as total'),
            DB::raw("SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft"),
            DB::raw("SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed"),
            DB::raw("SUM(CASE WHEN status = 'assigned' THEN 1 ELSE 0 END) as assigned")
        )->first();

        // ── Recent Activity ────────────────────────────
        $recent_activities = AuditTrail::with('user')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn($audit) => [
                'id' => $audit->id,
                'user' => $audit->user?->name,
                'event' => $audit->event,
                'model' => class_basename($audit->auditable_type),
                'created_at' => $audit->created_at->diffForHumans(),
            ]);

        // Cast SQLite float results to integers
        $castInt = fn($obj, ...$keys) => collect((array) $obj)
            ->map(fn($v, $k) => in_array($k, $keys) ? (int) $v : $v);

        $poStats = $castInt($poStats, 'total', 'draft', 'waiting_approval', 'approved', 'active');
        $quotationStats = $castInt($quotationStats, 'total', 'draft', 'sent', 'received', 'accepted');
        $requisitionStats = $castInt($requisitionStats, 'total', 'draft', 'submitted', 'approved');
        $pickingStats = $castInt($pickingStats, 'total', 'draft', 'confirmed', 'assigned');

        return $this->success([
            'stats' => $stats,
            'purchasing' => [
                'purchase_orders' => $poStats,
                'quotations' => $quotationStats,
                'requisitions' => $requisitionStats,
            ],
            'inventory' => [
                'total_value' => round($inventoryValue, 2),
                'total_on_hand' => round($totalOnHand, 2),
                'low_stock_products' => $lowStockProducts,
                'pickings' => $pickingStats,
            ],
            'recent_activities' => $recent_activities,
        ]);
    }

    public function notifications(Request $request): JsonResponse
    {
        $notifications = $this->notificationService->getUserNotifications($request->user());

        return $this->success($notifications);
    }

    public function unreadNotificationCount(Request $request): JsonResponse
    {
        $count = $this->notificationService->getUnreadCount($request->user());

        return $this->success(['count' => $count]);
    }

    public function markNotificationRead(Request $request, int $id): JsonResponse
    {
        $this->notificationService->markAsRead($request->user(), $id);

        return $this->success(null, 'Notification marked as read');
    }

    public function markAllNotificationsRead(Request $request): JsonResponse
    {
        $this->notificationService->markAllAsRead($request->user());

        return $this->success(null, 'All notifications marked as read');
    }

    public function auditLogs(Request $request): JsonResponse
    {
        $logs = $this->auditService->list($request->all());

        return $this->success($logs);
    }
}
