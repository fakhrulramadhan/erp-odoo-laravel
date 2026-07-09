<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Settings\SettingsController;
use App\Http\Controllers\Api\V1\Dashboard\DashboardController;
use App\Http\Controllers\Api\V1\MasterData\MasterDataController;
use App\Http\Controllers\Api\V1\Purchasing\PurchaseOrderController;
use App\Http\Controllers\Api\V1\Purchasing\SupplierQuotationController;
use App\Http\Controllers\Api\V1\Purchasing\PurchaseRequisitionController;
use App\Http\Controllers\Api\V1\Inventory\StockPickingController;
use App\Http\Controllers\Api\V1\Inventory\InventoryController;
use App\Http\Controllers\Api\V1\Reports\ReportController;
use App\Http\Controllers\Api\V1\Finance\FinanceController;
use App\Http\Controllers\Api\V1\Manufacturing\WorkCenterController;
use App\Http\Controllers\Api\V1\Manufacturing\RoutingController;
use App\Http\Controllers\Api\V1\Manufacturing\BillOfMaterialController;
use App\Http\Controllers\Api\V1\Manufacturing\BomRevisionController;
use App\Http\Controllers\Api\V1\Manufacturing\ManufacturingOrderController;
use App\Http\Controllers\Api\V1\Manufacturing\QualityControlPointController;
use App\Http\Controllers\Api\V1\Manufacturing\QualityCheckController;
use App\Http\Controllers\Api\V1\Manufacturing\ScrapOrderController;
use App\Http\Controllers\Api\V1\Manufacturing\EquipmentController;
use App\Http\Controllers\Api\V1\Manufacturing\MaintenanceOrderController;
use App\Http\Controllers\Api\V1\Manufacturing\AssetCategoryController;
use App\Http\Controllers\Api\V1\Manufacturing\AssetController;
use App\Http\Controllers\Api\V1\Manufacturing\ManufacturingDashboardController;

// Phase 5 — Enterprise Modules
use App\Http\Controllers\Api\V1\CRM\LeadController;
use App\Http\Controllers\Api\V1\CRM\OpportunityController;
use App\Http\Controllers\Api\V1\CRM\CrmActivityController;
use App\Http\Controllers\Api\V1\HRM\EmployeeController;
use App\Http\Controllers\Api\V1\HRM\EmployeeContractController;
use App\Http\Controllers\Api\V1\HRM\EmployeeDocumentController;
use App\Http\Controllers\Api\V1\HRM\WorkScheduleController;
use App\Http\Controllers\Api\V1\HRM\ShiftController;
use App\Http\Controllers\Api\V1\Attendance\AttendanceController;
use App\Http\Controllers\Api\V1\Attendance\AttendanceCorrectionController;
use App\Http\Controllers\Api\V1\Leave\LeaveTypeController;
use App\Http\Controllers\Api\V1\Leave\LeaveBalanceController;
use App\Http\Controllers\Api\V1\Leave\LeaveRequestController;
use App\Http\Controllers\Api\V1\Payroll\PayrollComponentController;
use App\Http\Controllers\Api\V1\Payroll\SalaryStructureController;
use App\Http\Controllers\Api\V1\Payroll\EmployeeSalaryController;
use App\Http\Controllers\Api\V1\Payroll\PayrollPeriodController;
use App\Http\Controllers\Api\V1\Payroll\PayrollController;
use App\Http\Controllers\Api\V1\Payroll\PayslipController;
use App\Http\Controllers\Api\V1\Recruitment\JobVacancyController;
use App\Http\Controllers\Api\V1\Recruitment\ApplicantController;
use App\Http\Controllers\Api\V1\Recruitment\InterviewController;
use App\Http\Controllers\Api\V1\Project\ProjectController;
use App\Http\Controllers\Api\V1\Project\TaskController;
use App\Http\Controllers\Api\V1\Project\MilestoneController;
use App\Http\Controllers\Api\V1\Project\SprintController;
use App\Http\Controllers\Api\V1\Project\TimesheetController;
use App\Http\Controllers\Api\V1\Helpdesk\TicketCategoryController;
use App\Http\Controllers\Api\V1\Helpdesk\SlaController;
use App\Http\Controllers\Api\V1\Helpdesk\TicketController;
use App\Http\Controllers\Api\V1\POS\PosSessionController;
use App\Http\Controllers\Api\V1\POS\PosOrderController;
use App\Http\Controllers\Api\V1\CMS\CmsPageController;
use App\Http\Controllers\Api\V1\CMS\CmsBlogController;
use App\Http\Controllers\Api\V1\CMS\CmsController;
use App\Http\Controllers\Api\V1\Ecommerce\EcommerceProductController;
use App\Http\Controllers\Api\V1\Ecommerce\EcommerceOrderController;
use App\Http\Controllers\Api\V1\Ecommerce\EcommerceSettingController;
use App\Http\Controllers\Api\V1\Ecommerce\ShippingMethodController;
use App\Http\Controllers\Api\V1\Ecommerce\PromoCodeController;
use App\Http\Controllers\Api\V1\Ecommerce\ProductReviewController;
use App\Http\Controllers\Api\V1\Document\DocumentController;
use App\Http\Controllers\Api\V1\Document\DocumentFolderController;
use App\Http\Controllers\Api\V1\Workflow\WorkflowController;
use App\Http\Controllers\Api\V1\Workflow\ApprovalController;
use App\Http\Controllers\Api\V1\BI\DashboardController as BiDashboardController;
use App\Http\Controllers\Api\V1\BI\SavedReportController;
use App\Http\Controllers\Api\V1\Notification\NotificationTemplateController;
use App\Http\Controllers\Api\V1\Notification\NotificationPreferenceController;
use App\Http\Controllers\Api\V1\Security\LoginHistoryController;
use App\Http\Controllers\Api\V1\Security\DeviceSessionController;
use App\Http\Controllers\Api\V1\Security\TwoFactorSettingController;
use App\Http\Controllers\Api\V1\MultiCompany\CompanyGroupController;
use App\Http\Controllers\Api\V1\MultiCompany\InterCompanyTransactionController;
use App\Http\Controllers\Api\V1\Expense\ExpenseClaimController;
use App\Http\Controllers\Api\V1\API\ApiKeyController;
use App\Http\Controllers\Api\V1\API\WebhookController;

/*
|--------------------------------------------------------------------------
| API Routes — V1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ─── Public Routes ─────────────────────────────
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/register', [AuthController::class, 'register']);

    // ─── Authenticated Routes ──────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);

        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index']);
        Route::get('dashboard/notifications', [DashboardController::class, 'notifications']);
        Route::get('dashboard/notifications/unread-count', [DashboardController::class, 'unreadNotificationCount']);
        Route::post('dashboard/notifications/{id}/read', [DashboardController::class, 'markNotificationRead']);
        Route::post('dashboard/notifications/read-all', [DashboardController::class, 'markAllNotificationsRead']);
        Route::get('dashboard/audit-logs', [DashboardController::class, 'auditLogs']);

        // Settings — Users
        Route::prefix('settings')->group(function () {
            Route::get('users', [SettingsController::class, 'users']);
            Route::post('users', [SettingsController::class, 'storeUser']);
            Route::get('users/{id}', [SettingsController::class, 'showUser']);
            Route::put('users/{id}', [SettingsController::class, 'updateUser']);
            Route::delete('users/{id}', [SettingsController::class, 'destroyUser']);

            // Companies
            Route::get('companies', [SettingsController::class, 'companies']);
            Route::post('companies', [SettingsController::class, 'storeCompany']);
            Route::get('companies/{id}', [SettingsController::class, 'showCompany']);
            Route::put('companies/{id}', [SettingsController::class, 'updateCompany']);
            Route::delete('companies/{id}', [SettingsController::class, 'destroyCompany']);

            // Currencies
            Route::get('currencies', [SettingsController::class, 'currencies']);
            Route::post('currencies', [SettingsController::class, 'storeCurrency']);

            // Tax Settings
            Route::get('tax-settings', [SettingsController::class, 'taxSettings']);
            Route::post('tax-settings', [SettingsController::class, 'storeTaxSetting']);

            // Numbering Sequences
            Route::get('numbering-sequences', [SettingsController::class, 'numberingSequences']);
            Route::post('numbering-sequences', [SettingsController::class, 'storeNumberingSequence']);
            Route::post('numbering-sequences/{id}/generate', [SettingsController::class, 'generateNumber']);

            // Branches, Departments, Positions
            Route::get('branches', [SettingsController::class, 'branches']);
            Route::get('departments', [SettingsController::class, 'departments']);
            Route::get('positions', [SettingsController::class, 'positions']);
        });

        // ─── Master Data ─────────────────────────────
        Route::prefix('master-data/{module}')->group(function () {
            Route::get('/', [MasterDataController::class, 'index']);
            Route::post('/', [MasterDataController::class, 'store']);
            Route::get('export', [MasterDataController::class, 'export']);
            Route::post('import', [MasterDataController::class, 'import']);
            Route::get('{id}', [MasterDataController::class, 'show']);
            Route::put('{id}', [MasterDataController::class, 'update']);
            Route::delete('{id}', [MasterDataController::class, 'destroy']);
            Route::patch('{id}/toggle-status', [MasterDataController::class, 'toggleStatus']);
        });

        // ─── Phase 2: Purchasing ────────────────────
        Route::prefix('purchasing')->group(function () {

            // Purchase Orders
            Route::get('purchase-orders', [PurchaseOrderController::class, 'index']);
            Route::post('purchase-orders', [PurchaseOrderController::class, 'store']);
            Route::get('purchase-orders/{id}', [PurchaseOrderController::class, 'show']);
            Route::put('purchase-orders/{id}', [PurchaseOrderController::class, 'update']);
            Route::delete('purchase-orders/{id}', [PurchaseOrderController::class, 'destroy']);
            Route::post('purchase-orders/{id}/submit', [PurchaseOrderController::class, 'submitForApproval']);
            Route::post('purchase-orders/{id}/approve', [PurchaseOrderController::class, 'approve']);
            Route::post('purchase-orders/{id}/cancel', [PurchaseOrderController::class, 'cancel']);
            Route::post('purchase-orders/{id}/send', [PurchaseOrderController::class, 'sendToVendor']);

            // Supplier Quotations
            Route::get('quotations', [SupplierQuotationController::class, 'index']);
            Route::post('quotations', [SupplierQuotationController::class, 'store']);
            Route::get('quotations/{id}', [SupplierQuotationController::class, 'show']);
            Route::put('quotations/{id}', [SupplierQuotationController::class, 'update']);
            Route::delete('quotations/{id}', [SupplierQuotationController::class, 'destroy']);
            Route::post('quotations/{id}/send', [SupplierQuotationController::class, 'sendToVendor']);
            Route::post('quotations/{id}/accept', [SupplierQuotationController::class, 'accept']);
            Route::post('quotations/{id}/reject', [SupplierQuotationController::class, 'reject']);

            // Purchase Requisitions
            Route::get('requisitions', [PurchaseRequisitionController::class, 'index']);
            Route::post('requisitions', [PurchaseRequisitionController::class, 'store']);
            Route::get('requisitions/{id}', [PurchaseRequisitionController::class, 'show']);
            Route::put('requisitions/{id}', [PurchaseRequisitionController::class, 'update']);
            Route::delete('requisitions/{id}', [PurchaseRequisitionController::class, 'destroy']);
            Route::post('requisitions/{id}/approve', [PurchaseRequisitionController::class, 'approve']);
            Route::post('requisitions/{id}/cancel', [PurchaseRequisitionController::class, 'cancel']);
        });

        // ─── Phase 2: Inventory ─────────────────────
        Route::prefix('inventory')->group(function () {

            // Stock Pickings
            Route::get('pickings', [StockPickingController::class, 'index']);
            Route::post('pickings', [StockPickingController::class, 'store']);
            Route::get('pickings/{id}', [StockPickingController::class, 'show']);
            Route::put('pickings/{id}', [StockPickingController::class, 'update']);
            Route::delete('pickings/{id}', [StockPickingController::class, 'destroy']);
            Route::post('pickings/{id}/confirm', [StockPickingController::class, 'confirm']);
            Route::post('pickings/{id}/validate', [StockPickingController::class, 'validate_picking']);
            Route::post('pickings/{id}/cancel', [StockPickingController::class, 'cancel']);

            // Stock Quants
            Route::get('quants', [InventoryController::class, 'quants']);
            Route::get('quants/product/{productId}', [InventoryController::class, 'productStock']);
            Route::get('quants/product/{productId}/on-hand', [InventoryController::class, 'totalOnHand']);
            Route::get('quants/low-stock', [InventoryController::class, 'lowStock']);

            // Inventory Adjustments
            Route::get('adjustments', [InventoryController::class, 'adjustments']);
            Route::post('adjustments', [InventoryController::class, 'createAdjustment']);
            Route::get('adjustments/{id}', [InventoryController::class, 'showAdjustment']);
            Route::put('adjustments/{id}', [InventoryController::class, 'updateAdjustment']);
            Route::post('adjustments/{id}/validate', [InventoryController::class, 'validateAdjustment']);
            Route::delete('adjustments/{id}', [InventoryController::class, 'deleteAdjustment']);
        });

        // ─── Finance & Accounting ───────────────────
        Route::prefix('finance')->group(function () {
            Route::get('dashboard', [FinanceController::class, 'dashboard']);
            Route::get('accounts', [FinanceController::class, 'accounts']);
            Route::get('journals', [FinanceController::class, 'journals']);
            Route::get('journal-entries', [FinanceController::class, 'journalEntries']);
            Route::post('journal-entries', [FinanceController::class, 'storeJournalEntry']);
            Route::post('journal-entries/{id}/post', [FinanceController::class, 'postJournalEntry']);
            Route::get('invoices', [FinanceController::class, 'invoices']);
            Route::post('invoices', [FinanceController::class, 'storeInvoice']);
            Route::post('invoices/{id}/post', [FinanceController::class, 'postInvoice']);
            Route::get('payments', [FinanceController::class, 'payments']);
            Route::post('payments', [FinanceController::class, 'storePayment']);
            Route::post('payments/{id}/post', [FinanceController::class, 'postPayment']);
            Route::get('taxes', [FinanceController::class, 'taxes']);
        });

        // ─── Reports ─────────────────────────────────
        Route::prefix('reports')->group(function () {
            Route::get('purchasing', [ReportController::class, 'purchasing']);
            Route::get('inventory', [ReportController::class, 'inventory']);
        });

        // ─── Phase 4: Manufacturing ─────────────────
        Route::prefix('manufacturing')->group(function () {

            // Work Centers
            Route::get('work-centers', [WorkCenterController::class, 'index']);
            Route::post('work-centers', [WorkCenterController::class, 'store']);
            Route::get('work-centers/{id}', [WorkCenterController::class, 'show']);
            Route::put('work-centers/{id}', [WorkCenterController::class, 'update']);
            Route::delete('work-centers/{id}', [WorkCenterController::class, 'destroy']);

            // Routings
            Route::get('routings', [RoutingController::class, 'index']);
            Route::post('routings', [RoutingController::class, 'store']);
            Route::get('routings/{id}', [RoutingController::class, 'show']);
            Route::put('routings/{id}', [RoutingController::class, 'update']);
            Route::delete('routings/{id}', [RoutingController::class, 'destroy']);

            // Bill of Materials
            Route::get('boms', [BillOfMaterialController::class, 'index']);
            Route::post('boms', [BillOfMaterialController::class, 'store']);
            Route::get('boms/{id}', [BillOfMaterialController::class, 'show']);
            Route::put('boms/{id}', [BillOfMaterialController::class, 'update']);
            Route::delete('boms/{id}', [BillOfMaterialController::class, 'destroy']);
            Route::post('boms/{id}/approve', [BillOfMaterialController::class, 'approve']);
            Route::post('boms/{id}/activate', [BillOfMaterialController::class, 'activate']);
            Route::post('boms/{id}/cancel', [BillOfMaterialController::class, 'cancel']);
            Route::post('boms/{id}/clone', [BillOfMaterialController::class, 'clone']);
            Route::get('boms/{bomId}/revisions', [BomRevisionController::class, 'index']);
            Route::get('boms/{bomId}/revisions/{id}', [BomRevisionController::class, 'show']);

            // Manufacturing Orders
            Route::get('orders', [ManufacturingOrderController::class, 'index']);
            Route::post('orders', [ManufacturingOrderController::class, 'store']);
            Route::get('orders/{id}', [ManufacturingOrderController::class, 'show']);
            Route::put('orders/{id}', [ManufacturingOrderController::class, 'update']);
            Route::delete('orders/{id}', [ManufacturingOrderController::class, 'destroy']);
            Route::post('orders/{id}/confirm', [ManufacturingOrderController::class, 'confirm']);
            Route::post('orders/{id}/reserve-materials', [ManufacturingOrderController::class, 'reserveMaterials']);
            Route::post('orders/{id}/start-production', [ManufacturingOrderController::class, 'startProduction']);
            Route::post('orders/{id}/finish-production', [ManufacturingOrderController::class, 'finishProduction']);
            Route::post('orders/{id}/mark-finished', [ManufacturingOrderController::class, 'markFinished']);
            Route::post('orders/{id}/close', [ManufacturingOrderController::class, 'close']);
            Route::post('orders/{id}/cancel', [ManufacturingOrderController::class, 'cancel']);

            // Quality Control
            Route::get('quality/control-points', [QualityControlPointController::class, 'index']);
            Route::post('quality/control-points', [QualityControlPointController::class, 'store']);
            Route::get('quality/control-points/{id}', [QualityControlPointController::class, 'show']);
            Route::put('quality/control-points/{id}', [QualityControlPointController::class, 'update']);
            Route::delete('quality/control-points/{id}', [QualityControlPointController::class, 'destroy']);

            Route::get('quality/checks', [QualityCheckController::class, 'index']);
            Route::post('quality/checks', [QualityCheckController::class, 'store']);
            Route::get('quality/checks/{id}', [QualityCheckController::class, 'show']);
            Route::put('quality/checks/{id}', [QualityCheckController::class, 'update']);
            Route::delete('quality/checks/{id}', [QualityCheckController::class, 'destroy']);
            Route::post('quality/checks/{id}/start', [QualityCheckController::class, 'startInspection']);
            Route::post('quality/checks/{id}/pass', [QualityCheckController::class, 'pass']);
            Route::post('quality/checks/{id}/fail', [QualityCheckController::class, 'fail']);

            // Scrap Orders
            Route::get('scraps', [ScrapOrderController::class, 'index']);
            Route::post('scraps', [ScrapOrderController::class, 'store']);
            Route::get('scraps/{id}', [ScrapOrderController::class, 'show']);
            Route::delete('scraps/{id}', [ScrapOrderController::class, 'destroy']);
            Route::post('scraps/{id}/confirm', [ScrapOrderController::class, 'confirm']);
            Route::post('scraps/{id}/process', [ScrapOrderController::class, 'process']);
            Route::post('scraps/{id}/cancel', [ScrapOrderController::class, 'cancel']);

            // Equipment
            Route::get('equipment', [EquipmentController::class, 'index']);
            Route::post('equipment', [EquipmentController::class, 'store']);
            Route::get('equipment/{id}', [EquipmentController::class, 'show']);
            Route::put('equipment/{id}', [EquipmentController::class, 'update']);
            Route::delete('equipment/{id}', [EquipmentController::class, 'destroy']);

            // Maintenance Orders
            Route::get('maintenance', [MaintenanceOrderController::class, 'index']);
            Route::post('maintenance', [MaintenanceOrderController::class, 'store']);
            Route::get('maintenance/{id}', [MaintenanceOrderController::class, 'show']);
            Route::put('maintenance/{id}', [MaintenanceOrderController::class, 'update']);
            Route::delete('maintenance/{id}', [MaintenanceOrderController::class, 'destroy']);
            Route::post('maintenance/{id}/schedule', [MaintenanceOrderController::class, 'schedule']);
            Route::post('maintenance/{id}/start', [MaintenanceOrderController::class, 'startWork']);
            Route::post('maintenance/{id}/complete', [MaintenanceOrderController::class, 'complete']);
            Route::post('maintenance/{id}/cancel', [MaintenanceOrderController::class, 'cancel']);

            // Asset Categories
            Route::get('asset-categories', [AssetCategoryController::class, 'index']);
            Route::post('asset-categories', [AssetCategoryController::class, 'store']);
            Route::get('asset-categories/{id}', [AssetCategoryController::class, 'show']);
            Route::put('asset-categories/{id}', [AssetCategoryController::class, 'update']);
            Route::delete('asset-categories/{id}', [AssetCategoryController::class, 'destroy']);

            // Assets
            Route::get('assets', [AssetController::class, 'index']);
            Route::post('assets', [AssetController::class, 'store']);
            Route::get('assets/{id}', [AssetController::class, 'show']);
            Route::put('assets/{id}', [AssetController::class, 'update']);
            Route::delete('assets/{id}', [AssetController::class, 'destroy']);
            Route::post('assets/{id}/activate', [AssetController::class, 'activate']);
            Route::post('assets/{id}/transfer', [AssetController::class, 'transfer']);
            Route::post('assets/{id}/dispose', [AssetController::class, 'dispose']);
            Route::post('assets/{id}/depreciate', [AssetController::class, 'depreciate']);

            // Dashboard & Reports
            Route::get('dashboard', [ManufacturingDashboardController::class, 'summary']);
            Route::get('dashboard/production-trend', [ManufacturingDashboardController::class, 'productionTrend']);
            Route::get('reports/production', [ManufacturingDashboardController::class, 'productionReport']);
            Route::get('reports/quality', [ManufacturingDashboardController::class, 'qualityReport']);
            Route::get('reports/costs', [ManufacturingDashboardController::class, 'costReport']);
            Route::get('reports/maintenance', [ManufacturingDashboardController::class, 'maintenanceReport']);
            Route::get('reports/assets', [ManufacturingDashboardController::class, 'assetReport']);
            Route::get('reports/scrap', [ManufacturingDashboardController::class, 'scrapReport']);
        });

        // ─── Phase 5: CRM ────────────────────────────
        Route::prefix('crm')->group(function () {
            // Leads
            Route::get('leads', [LeadController::class, 'index']);
            Route::post('leads', [LeadController::class, 'store']);
            Route::get('leads/{id}', [LeadController::class, 'show']);
            Route::put('leads/{id}', [LeadController::class, 'update']);
            Route::delete('leads/{id}', [LeadController::class, 'destroy']);
            Route::post('leads/{id}/qualify', [LeadController::class, 'qualify']);
            Route::post('leads/{id}/mark-as-lost', [LeadController::class, 'markAsLost']);

            // Opportunities
            Route::get('opportunities', [OpportunityController::class, 'index']);
            Route::post('opportunities', [OpportunityController::class, 'store']);
            Route::get('opportunities/{id}', [OpportunityController::class, 'show']);
            Route::put('opportunities/{id}', [OpportunityController::class, 'update']);
            Route::delete('opportunities/{id}', [OpportunityController::class, 'destroy']);
            Route::put('opportunities/{id}/stage', [OpportunityController::class, 'updateStage']);

            // Activities
            Route::get('activities', [CrmActivityController::class, 'index']);
            Route::post('activities', [CrmActivityController::class, 'store']);
            Route::get('activities/{id}', [CrmActivityController::class, 'show']);
            Route::put('activities/{id}', [CrmActivityController::class, 'update']);
            Route::delete('activities/{id}', [CrmActivityController::class, 'destroy']);
            Route::post('activities/{id}/complete', [CrmActivityController::class, 'complete']);
        });

        // ─── Phase 5: HRM ────────────────────────────
        Route::prefix('hrm')->group(function () {
            // Employees
            Route::get('employees', [EmployeeController::class, 'index']);
            Route::post('employees', [EmployeeController::class, 'store']);
            Route::get('employees/{id}', [EmployeeController::class, 'show']);
            Route::put('employees/{id}', [EmployeeController::class, 'update']);
            Route::delete('employees/{id}', [EmployeeController::class, 'destroy']);
            Route::post('employees/{id}/terminate', [EmployeeController::class, 'terminate']);

            // Employee Contracts
            Route::get('contracts', [EmployeeContractController::class, 'index']);
            Route::post('contracts', [EmployeeContractController::class, 'store']);
            Route::get('contracts/{id}', [EmployeeContractController::class, 'show']);
            Route::put('contracts/{id}', [EmployeeContractController::class, 'update']);
            Route::delete('contracts/{id}', [EmployeeContractController::class, 'destroy']);

            // Employee Documents
            Route::get('documents', [EmployeeDocumentController::class, 'index']);
            Route::post('documents', [EmployeeDocumentController::class, 'store']);
            Route::get('documents/{id}', [EmployeeDocumentController::class, 'show']);
            Route::put('documents/{id}', [EmployeeDocumentController::class, 'update']);
            Route::delete('documents/{id}', [EmployeeDocumentController::class, 'destroy']);

            // Work Schedules
            Route::get('work-schedules', [WorkScheduleController::class, 'index']);
            Route::post('work-schedules', [WorkScheduleController::class, 'store']);
            Route::get('work-schedules/{id}', [WorkScheduleController::class, 'show']);
            Route::put('work-schedules/{id}', [WorkScheduleController::class, 'update']);
            Route::delete('work-schedules/{id}', [WorkScheduleController::class, 'destroy']);

            // Shifts
            Route::get('shifts', [ShiftController::class, 'index']);
            Route::post('shifts', [ShiftController::class, 'store']);
            Route::get('shifts/{id}', [ShiftController::class, 'show']);
            Route::put('shifts/{id}', [ShiftController::class, 'update']);
            Route::delete('shifts/{id}', [ShiftController::class, 'destroy']);
        });

        // ─── Phase 5: Attendance ──────────────────────
        Route::prefix('attendance')->group(function () {
            // Attendance Records
            Route::get('records', [AttendanceController::class, 'index']);
            Route::post('records', [AttendanceController::class, 'store']);
            Route::get('records/{id}', [AttendanceController::class, 'show']);
            Route::put('records/{id}', [AttendanceController::class, 'update']);
            Route::delete('records/{id}', [AttendanceController::class, 'destroy']);
            Route::post('check-in', [AttendanceController::class, 'checkIn']);
            Route::post('{id}/check-out', [AttendanceController::class, 'checkOut']);

            // Corrections
            Route::get('corrections', [AttendanceCorrectionController::class, 'index']);
            Route::post('corrections', [AttendanceCorrectionController::class, 'store']);
            Route::get('corrections/{id}', [AttendanceCorrectionController::class, 'show']);
            Route::post('corrections/{id}/approve', [AttendanceCorrectionController::class, 'approve']);
            Route::post('corrections/{id}/reject', [AttendanceCorrectionController::class, 'reject']);
        });

        // ─── Phase 5: Leave ──────────────────────────
        Route::prefix('leave')->group(function () {
            // Leave Types
            Route::get('types', [LeaveTypeController::class, 'index']);
            Route::post('types', [LeaveTypeController::class, 'store']);
            Route::get('types/{id}', [LeaveTypeController::class, 'show']);
            Route::put('types/{id}', [LeaveTypeController::class, 'update']);
            Route::delete('types/{id}', [LeaveTypeController::class, 'destroy']);

            // Leave Balances
            Route::get('balances', [LeaveBalanceController::class, 'index']);
            Route::post('balances', [LeaveBalanceController::class, 'store']);
            Route::get('balances/{id}', [LeaveBalanceController::class, 'show']);
            Route::put('balances/{id}', [LeaveBalanceController::class, 'update']);
            Route::delete('balances/{id}', [LeaveBalanceController::class, 'destroy']);
            Route::post('balances/initialize-year', [LeaveBalanceController::class, 'initializeYear']);

            // Leave Requests
            Route::get('requests', [LeaveRequestController::class, 'index']);
            Route::post('requests', [LeaveRequestController::class, 'store']);
            Route::get('requests/{id}', [LeaveRequestController::class, 'show']);
            Route::put('requests/{id}', [LeaveRequestController::class, 'update']);
            Route::delete('requests/{id}', [LeaveRequestController::class, 'destroy']);
            Route::post('requests/{id}/approve', [LeaveRequestController::class, 'approve']);
            Route::post('requests/{id}/reject', [LeaveRequestController::class, 'reject']);
            Route::post('requests/{id}/cancel', [LeaveRequestController::class, 'cancel']);
        });

        // ─── Phase 5: Payroll ────────────────────────
        Route::prefix('payroll')->group(function () {
            // Components
            Route::get('components', [PayrollComponentController::class, 'index']);
            Route::post('components', [PayrollComponentController::class, 'store']);
            Route::get('components/{id}', [PayrollComponentController::class, 'show']);
            Route::put('components/{id}', [PayrollComponentController::class, 'update']);
            Route::delete('components/{id}', [PayrollComponentController::class, 'destroy']);

            // Salary Structures
            Route::get('salary-structures', [SalaryStructureController::class, 'index']);
            Route::post('salary-structures', [SalaryStructureController::class, 'store']);
            Route::get('salary-structures/{id}', [SalaryStructureController::class, 'show']);
            Route::put('salary-structures/{id}', [SalaryStructureController::class, 'update']);
            Route::delete('salary-structures/{id}', [SalaryStructureController::class, 'destroy']);

            // Employee Salaries
            Route::get('employee-salaries', [EmployeeSalaryController::class, 'index']);
            Route::post('employee-salaries', [EmployeeSalaryController::class, 'store']);
            Route::get('employee-salaries/{id}', [EmployeeSalaryController::class, 'show']);
            Route::put('employee-salaries/{id}', [EmployeeSalaryController::class, 'update']);
            Route::delete('employee-salaries/{id}', [EmployeeSalaryController::class, 'destroy']);

            // Periods
            Route::get('periods', [PayrollPeriodController::class, 'index']);
            Route::post('periods', [PayrollPeriodController::class, 'store']);
            Route::get('periods/{id}', [PayrollPeriodController::class, 'show']);
            Route::put('periods/{id}', [PayrollPeriodController::class, 'update']);
            Route::delete('periods/{id}', [PayrollPeriodController::class, 'destroy']);

            // Payroll Runs
            Route::get('runs', [PayrollController::class, 'index']);
            Route::post('runs', [PayrollController::class, 'store']);
            Route::get('runs/{id}', [PayrollController::class, 'show']);
            Route::put('runs/{id}', [PayrollController::class, 'update']);
            Route::delete('runs/{id}', [PayrollController::class, 'destroy']);
            Route::post('runs/{id}/process', [PayrollController::class, 'process']);
            Route::post('runs/{id}/confirm', [PayrollController::class, 'confirm']);
            Route::post('runs/{id}/approve', [PayrollController::class, 'approve']);
            Route::post('runs/{id}/cancel', [PayrollController::class, 'cancel']);

            // Payslips
            Route::get('payslips', [PayslipController::class, 'index']);
            Route::post('payslips', [PayslipController::class, 'store']);
            Route::get('payslips/{id}', [PayslipController::class, 'show']);
            Route::delete('payslips/{id}', [PayslipController::class, 'destroy']);
            Route::post('payslips/{id}/confirm', [PayslipController::class, 'confirm']);
            Route::post('payslips/{id}/pay', [PayslipController::class, 'pay']);
        });

        // ─── Phase 5: Recruitment ────────────────────
        Route::prefix('recruitment')->group(function () {
            // Vacancies
            Route::get('vacancies', [JobVacancyController::class, 'index']);
            Route::post('vacancies', [JobVacancyController::class, 'store']);
            Route::get('vacancies/{id}', [JobVacancyController::class, 'show']);
            Route::put('vacancies/{id}', [JobVacancyController::class, 'update']);
            Route::delete('vacancies/{id}', [JobVacancyController::class, 'destroy']);
            Route::post('vacancies/{id}/publish', [JobVacancyController::class, 'publish']);
            Route::post('vacancies/{id}/close', [JobVacancyController::class, 'close']);

            // Applicants
            Route::get('applicants', [ApplicantController::class, 'index']);
            Route::post('applicants', [ApplicantController::class, 'store']);
            Route::get('applicants/{id}', [ApplicantController::class, 'show']);
            Route::put('applicants/{id}', [ApplicantController::class, 'update']);
            Route::delete('applicants/{id}', [ApplicantController::class, 'destroy']);
            Route::put('applicants/{id}/stage', [ApplicantController::class, 'updateStage']);

            // Interviews
            Route::get('interviews', [InterviewController::class, 'index']);
            Route::post('interviews', [InterviewController::class, 'store']);
            Route::get('interviews/{id}', [InterviewController::class, 'show']);
            Route::put('interviews/{id}', [InterviewController::class, 'update']);
            Route::delete('interviews/{id}', [InterviewController::class, 'destroy']);
            Route::post('interviews/{id}/feedback', [InterviewController::class, 'submitFeedback']);
        });

        // ─── Phase 5: Project Management ─────────────
        Route::prefix('project')->group(function () {
            // Projects
            Route::get('projects', [ProjectController::class, 'index']);
            Route::post('projects', [ProjectController::class, 'store']);
            Route::get('projects/{id}', [ProjectController::class, 'show']);
            Route::put('projects/{id}', [ProjectController::class, 'update']);
            Route::delete('projects/{id}', [ProjectController::class, 'destroy']);
            Route::put('projects/{id}/status', [ProjectController::class, 'updateStatus']);

            // Tasks
            Route::get('tasks', [TaskController::class, 'index']);
            Route::post('tasks', [TaskController::class, 'store']);
            Route::get('tasks/{id}', [TaskController::class, 'show']);
            Route::put('tasks/{id}', [TaskController::class, 'update']);
            Route::delete('tasks/{id}', [TaskController::class, 'destroy']);
            Route::put('tasks/{id}/status', [TaskController::class, 'updateStatus']);
            Route::post('tasks/{id}/comment', [TaskController::class, 'addComment']);

            // Milestones
            Route::get('milestones', [MilestoneController::class, 'index']);
            Route::post('milestones', [MilestoneController::class, 'store']);
            Route::get('milestones/{id}', [MilestoneController::class, 'show']);
            Route::put('milestones/{id}', [MilestoneController::class, 'update']);
            Route::delete('milestones/{id}', [MilestoneController::class, 'destroy']);

            // Sprints
            Route::get('sprints', [SprintController::class, 'index']);
            Route::post('sprints', [SprintController::class, 'store']);
            Route::get('sprints/{id}', [SprintController::class, 'show']);
            Route::put('sprints/{id}', [SprintController::class, 'update']);
            Route::delete('sprints/{id}', [SprintController::class, 'destroy']);
            Route::post('sprints/{id}/start', [SprintController::class, 'start']);
            Route::post('sprints/{id}/complete', [SprintController::class, 'complete']);

            // Timesheets
            Route::get('timesheets', [TimesheetController::class, 'index']);
            Route::post('timesheets', [TimesheetController::class, 'store']);
            Route::get('timesheets/{id}', [TimesheetController::class, 'show']);
            Route::put('timesheets/{id}', [TimesheetController::class, 'update']);
            Route::delete('timesheets/{id}', [TimesheetController::class, 'destroy']);
            Route::post('timesheets/{id}/approve', [TimesheetController::class, 'approve']);
        });

        // ─── Phase 5: Helpdesk ───────────────────────
        Route::prefix('helpdesk')->group(function () {
            // Categories
            Route::get('categories', [TicketCategoryController::class, 'index']);
            Route::post('categories', [TicketCategoryController::class, 'store']);
            Route::get('categories/{id}', [TicketCategoryController::class, 'show']);
            Route::put('categories/{id}', [TicketCategoryController::class, 'update']);
            Route::delete('categories/{id}', [TicketCategoryController::class, 'destroy']);

            // SLA
            Route::get('slas', [SlaController::class, 'index']);
            Route::post('slas', [SlaController::class, 'store']);
            Route::get('slas/{id}', [SlaController::class, 'show']);
            Route::put('slas/{id}', [SlaController::class, 'update']);
            Route::delete('slas/{id}', [SlaController::class, 'destroy']);

            // Tickets
            Route::get('tickets', [TicketController::class, 'index']);
            Route::post('tickets', [TicketController::class, 'store']);
            Route::get('tickets/{id}', [TicketController::class, 'show']);
            Route::put('tickets/{id}', [TicketController::class, 'update']);
            Route::delete('tickets/{id}', [TicketController::class, 'destroy']);
            Route::post('tickets/{id}/assign', [TicketController::class, 'assign']);
            Route::post('tickets/{id}/resolve', [TicketController::class, 'resolve']);
            Route::post('tickets/{id}/close', [TicketController::class, 'close']);
            Route::post('tickets/{id}/reply', [TicketController::class, 'addReply']);
        });

        // ─── Phase 5: POS ────────────────────────────
        Route::prefix('pos')->group(function () {
            // Sessions
            Route::get('sessions', [PosSessionController::class, 'index']);
            Route::get('sessions/{id}', [PosSessionController::class, 'show']);
            Route::delete('sessions/{id}', [PosSessionController::class, 'destroy']);
            Route::post('sessions/open', [PosSessionController::class, 'open']);
            Route::post('sessions/{id}/close', [PosSessionController::class, 'close']);

            // Orders
            Route::get('orders', [PosOrderController::class, 'index']);
            Route::post('orders', [PosOrderController::class, 'store']);
            Route::get('orders/{id}', [PosOrderController::class, 'show']);
            Route::delete('orders/{id}', [PosOrderController::class, 'destroy']);
            Route::post('orders/{id}/refund', [PosOrderController::class, 'refund']);
        });

        // ─── Phase 5: CMS ────────────────────────────
        Route::prefix('cms')->group(function () {
            // Pages
            Route::get('pages', [CmsPageController::class, 'index']);
            Route::post('pages', [CmsPageController::class, 'store']);
            Route::get('pages/{id}', [CmsPageController::class, 'show']);
            Route::put('pages/{id}', [CmsPageController::class, 'update']);
            Route::delete('pages/{id}', [CmsPageController::class, 'destroy']);
            Route::post('pages/{id}/publish', [CmsPageController::class, 'publish']);
            Route::post('pages/{id}/unpublish', [CmsPageController::class, 'unpublish']);

            // Blogs
            Route::get('blogs', [CmsBlogController::class, 'index']);
            Route::post('blogs', [CmsBlogController::class, 'store']);
            Route::get('blogs/{id}', [CmsBlogController::class, 'show']);
            Route::put('blogs/{id}', [CmsBlogController::class, 'update']);
            Route::delete('blogs/{id}', [CmsBlogController::class, 'destroy']);
            Route::post('blogs/{id}/publish', [CmsBlogController::class, 'publish']);

            // Menus
            Route::get('menus', [CmsController::class, 'indexMenus']);
            Route::post('menus', [CmsController::class, 'storeMenu']);
            Route::get('menus/{id}', [CmsController::class, 'showMenu']);
            Route::put('menus/{id}', [CmsController::class, 'updateMenu']);
            Route::delete('menus/{id}', [CmsController::class, 'destroyMenu']);

            // Categories
            Route::get('blog-categories', [CmsController::class, 'indexCategories']);
            Route::post('blog-categories', [CmsController::class, 'storeCategory']);
            Route::get('blog-categories/{id}', [CmsController::class, 'showCategory']);
            Route::put('blog-categories/{id}', [CmsController::class, 'updateCategory']);
            Route::delete('blog-categories/{id}', [CmsController::class, 'destroyCategory']);

            // Tags
            Route::get('tags', [CmsController::class, 'indexTags']);
            Route::post('tags', [CmsController::class, 'storeTag']);
            Route::get('tags/{id}', [CmsController::class, 'showTag']);
            Route::put('tags/{id}', [CmsController::class, 'updateTag']);
            Route::delete('tags/{id}', [CmsController::class, 'destroyTag']);

            // Banners
            Route::get('banners', [CmsController::class, 'indexBanners']);
            Route::post('banners', [CmsController::class, 'storeBanner']);
            Route::get('banners/{id}', [CmsController::class, 'showBanner']);
            Route::put('banners/{id}', [CmsController::class, 'updateBanner']);
            Route::delete('banners/{id}', [CmsController::class, 'destroyBanner']);

            // Media
            Route::get('media', [CmsController::class, 'indexMedia']);
            Route::post('media', [CmsController::class, 'storeMedia']);
            Route::get('media/{id}', [CmsController::class, 'showMedia']);
            Route::delete('media/{id}', [CmsController::class, 'destroyMedia']);
        });

        // ─── Phase 5: E-Commerce ─────────────────────
        Route::prefix('ecommerce')->group(function () {
            // Products
            Route::get('products', [EcommerceProductController::class, 'index']);
            Route::post('products', [EcommerceProductController::class, 'store']);
            Route::get('products/{id}', [EcommerceProductController::class, 'show']);
            Route::put('products/{id}', [EcommerceProductController::class, 'update']);
            Route::delete('products/{id}', [EcommerceProductController::class, 'destroy']);

            // Orders
            Route::get('orders', [EcommerceOrderController::class, 'index']);
            Route::post('orders', [EcommerceOrderController::class, 'store']);
            Route::get('orders/{id}', [EcommerceOrderController::class, 'show']);
            Route::put('orders/{id}', [EcommerceOrderController::class, 'update']);
            Route::delete('orders/{id}', [EcommerceOrderController::class, 'destroy']);
            Route::put('orders/{id}/status', [EcommerceOrderController::class, 'updateStatus']);

            // Settings
            Route::get('settings', [EcommerceSettingController::class, 'index']);
            Route::post('settings', [EcommerceSettingController::class, 'store']);
            Route::get('settings/{id}', [EcommerceSettingController::class, 'show']);
            Route::put('settings/{id}', [EcommerceSettingController::class, 'update']);

            // Shipping Methods
            Route::get('shipping-methods', [ShippingMethodController::class, 'index']);
            Route::post('shipping-methods', [ShippingMethodController::class, 'store']);
            Route::get('shipping-methods/{id}', [ShippingMethodController::class, 'show']);
            Route::put('shipping-methods/{id}', [ShippingMethodController::class, 'update']);
            Route::delete('shipping-methods/{id}', [ShippingMethodController::class, 'destroy']);

            // Promo Codes
            Route::get('promo-codes', [PromoCodeController::class, 'index']);
            Route::post('promo-codes', [PromoCodeController::class, 'store']);
            Route::get('promo-codes/{id}', [PromoCodeController::class, 'show']);
            Route::put('promo-codes/{id}', [PromoCodeController::class, 'update']);
            Route::delete('promo-codes/{id}', [PromoCodeController::class, 'destroy']);
            Route::post('promo-codes/validate', [PromoCodeController::class, 'validate']);

            // Reviews
            Route::get('reviews', [ProductReviewController::class, 'index']);
            Route::post('reviews', [ProductReviewController::class, 'store']);
            Route::get('reviews/{id}', [ProductReviewController::class, 'show']);
            Route::delete('reviews/{id}', [ProductReviewController::class, 'destroy']);
            Route::post('reviews/{id}/approve', [ProductReviewController::class, 'approve']);
        });

        // ─── Phase 5: Document Management ────────────
        Route::prefix('documents')->group(function () {
            Route::get('/', [DocumentController::class, 'index']);
            Route::post('/', [DocumentController::class, 'store']);
            Route::get('{id}', [DocumentController::class, 'show']);
            Route::put('{id}', [DocumentController::class, 'update']);
            Route::delete('{id}', [DocumentController::class, 'destroy']);
            Route::post('{id}/versions', [DocumentController::class, 'addVersion']);

            // Folders
            Route::get('folders', [DocumentFolderController::class, 'index']);
            Route::post('folders', [DocumentFolderController::class, 'store']);
            Route::get('folders/{id}', [DocumentFolderController::class, 'show']);
            Route::put('folders/{id}', [DocumentFolderController::class, 'update']);
            Route::delete('folders/{id}', [DocumentFolderController::class, 'destroy']);
        });

        // ─── Phase 5: Workflow Automation ────────────
        Route::prefix('workflow')->group(function () {
            // Workflows
            Route::get('workflows', [WorkflowController::class, 'index']);
            Route::post('workflows', [WorkflowController::class, 'store']);
            Route::get('workflows/{id}', [WorkflowController::class, 'show']);
            Route::put('workflows/{id}', [WorkflowController::class, 'update']);
            Route::delete('workflows/{id}', [WorkflowController::class, 'destroy']);
            Route::post('workflows/{id}/activate', [WorkflowController::class, 'activate']);
            Route::post('workflows/{id}/deactivate', [WorkflowController::class, 'deactivate']);

            // Approvals
            Route::get('approvals', [ApprovalController::class, 'index']);
            Route::post('approvals', [ApprovalController::class, 'store']);
            Route::get('approvals/{id}', [ApprovalController::class, 'show']);
            Route::post('approvals/{id}/approve', [ApprovalController::class, 'approve']);
            Route::post('approvals/{id}/reject', [ApprovalController::class, 'reject']);
        });

        // ─── Phase 5: BI Dashboard ───────────────────
        Route::prefix('bi')->group(function () {
            // Dashboards
            Route::get('dashboards', [BiDashboardController::class, 'index']);
            Route::post('dashboards', [BiDashboardController::class, 'store']);
            Route::get('dashboards/{id}', [BiDashboardController::class, 'show']);
            Route::put('dashboards/{id}', [BiDashboardController::class, 'update']);
            Route::delete('dashboards/{id}', [BiDashboardController::class, 'destroy']);

            // Saved Reports
            Route::get('reports', [SavedReportController::class, 'index']);
            Route::post('reports', [SavedReportController::class, 'store']);
            Route::get('reports/{id}', [SavedReportController::class, 'show']);
            Route::put('reports/{id}', [SavedReportController::class, 'update']);
            Route::delete('reports/{id}', [SavedReportController::class, 'destroy']);
            Route::post('reports/{id}/execute', [SavedReportController::class, 'execute']);
        });

        // ─── Phase 5: Notification Center ────────────
        Route::prefix('notifications')->group(function () {
            // Templates
            Route::get('templates', [NotificationTemplateController::class, 'index']);
            Route::post('templates', [NotificationTemplateController::class, 'store']);
            Route::get('templates/{id}', [NotificationTemplateController::class, 'show']);
            Route::put('templates/{id}', [NotificationTemplateController::class, 'update']);
            Route::delete('templates/{id}', [NotificationTemplateController::class, 'destroy']);

            // Preferences
            Route::get('preferences', [NotificationPreferenceController::class, 'index']);
            Route::post('preferences', [NotificationPreferenceController::class, 'store']);
            Route::get('preferences/{id}', [NotificationPreferenceController::class, 'show']);
            Route::delete('preferences/{id}', [NotificationPreferenceController::class, 'destroy']);
        });

        // ─── Phase 5: Security & Audit ───────────────
        Route::prefix('security')->group(function () {
            // Login History
            Route::get('login-history', [LoginHistoryController::class, 'index']);
            Route::get('login-history/{id}', [LoginHistoryController::class, 'show']);

            // Device Sessions
            Route::get('sessions', [DeviceSessionController::class, 'index']);
            Route::get('sessions/{id}', [DeviceSessionController::class, 'show']);
            Route::delete('sessions/{id}/revoke', [DeviceSessionController::class, 'revoke']);
            Route::post('sessions/revoke-all', [DeviceSessionController::class, 'revokeAll']);

            // Two-Factor
            Route::get('two-factor/{id}', [TwoFactorSettingController::class, 'show']);
            Route::post('two-factor', [TwoFactorSettingController::class, 'store']);
            Route::post('two-factor/enable', [TwoFactorSettingController::class, 'enable']);
            Route::post('two-factor/disable', [TwoFactorSettingController::class, 'disable']);
        });

        // ─── Phase 5: Multi-Company ──────────────────
        Route::prefix('multi-company')->group(function () {
            // Company Groups
            Route::get('groups', [CompanyGroupController::class, 'index']);
            Route::post('groups', [CompanyGroupController::class, 'store']);
            Route::get('groups/{id}', [CompanyGroupController::class, 'show']);
            Route::put('groups/{id}', [CompanyGroupController::class, 'update']);
            Route::delete('groups/{id}', [CompanyGroupController::class, 'destroy']);

            // Inter-Company Transactions
            Route::get('transactions', [InterCompanyTransactionController::class, 'index']);
            Route::post('transactions', [InterCompanyTransactionController::class, 'store']);
            Route::get('transactions/{id}', [InterCompanyTransactionController::class, 'show']);
            Route::put('transactions/{id}', [InterCompanyTransactionController::class, 'update']);
            Route::delete('transactions/{id}', [InterCompanyTransactionController::class, 'destroy']);
            Route::post('transactions/{id}/approve', [InterCompanyTransactionController::class, 'approve']);
            Route::post('transactions/{id}/reject', [InterCompanyTransactionController::class, 'reject']);
        });

        // ─── Phase 5: Expense Management ─────────────
        Route::prefix('expenses')->group(function () {
            Route::get('/', [ExpenseClaimController::class, 'index']);
            Route::post('/', [ExpenseClaimController::class, 'store']);
            Route::get('{id}', [ExpenseClaimController::class, 'show']);
            Route::put('{id}', [ExpenseClaimController::class, 'update']);
            Route::delete('{id}', [ExpenseClaimController::class, 'destroy']);
            Route::post('{id}/submit', [ExpenseClaimController::class, 'submit']);
            Route::post('{id}/approve', [ExpenseClaimController::class, 'approve']);
            Route::post('{id}/reject', [ExpenseClaimController::class, 'reject']);
            Route::post('{id}/mark-paid', [ExpenseClaimController::class, 'markPaid']);
        });

        // ─── Phase 5: API Gateway ────────────────────
        Route::prefix('api-gateway')->group(function () {
            // API Keys
            Route::get('keys', [ApiKeyController::class, 'index']);
            Route::post('keys', [ApiKeyController::class, 'store']);
            Route::get('keys/{id}', [ApiKeyController::class, 'show']);
            Route::put('keys/{id}', [ApiKeyController::class, 'update']);
            Route::delete('keys/{id}', [ApiKeyController::class, 'destroy']);
            Route::post('keys/{id}/revoke', [ApiKeyController::class, 'revoke']);

            // Webhooks
            Route::get('webhooks', [WebhookController::class, 'index']);
            Route::post('webhooks', [WebhookController::class, 'store']);
            Route::get('webhooks/{id}', [WebhookController::class, 'show']);
            Route::put('webhooks/{id}', [WebhookController::class, 'update']);
            Route::delete('webhooks/{id}', [WebhookController::class, 'destroy']);
            Route::post('webhooks/{id}/toggle', [WebhookController::class, 'toggle']);
        });
    });
});
