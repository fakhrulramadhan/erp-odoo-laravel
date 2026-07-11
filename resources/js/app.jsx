import { BrowserRouter, Routes, Route, Navigate, Outlet } from 'react-router-dom';
import { AuthProvider, useAuth } from './contexts/AuthContext';
import { PageLoader } from './components/ui';
import DashboardLayout from './components/layout/DashboardLayout';
import LoginPage from './pages/auth/LoginPage';
import DashboardPage from './pages/dashboard/DashboardPage';

// Master Data Pages
import ProductCategoriesPage from './pages/master-data/ProductCategoriesPage';
import UnitOfMeasuresPage from './pages/master-data/UnitOfMeasuresPage';
import ProductsPage from './pages/master-data/ProductsPage';
import CustomersPage from './pages/master-data/CustomersPage';
import VendorsPage from './pages/master-data/VendorsPage';
import WarehousesPage from './pages/master-data/WarehousesPage';
import StockLocationsPage from './pages/master-data/StockLocationsPage';
import BankAccountsPage from './pages/master-data/BankAccountsPage';
import PaymentMethodsPage from './pages/master-data/PaymentMethodsPage';
import ChartOfAccountsPage from './pages/master-data/ChartOfAccountsPage';

// Phase 2: Purchasing Pages
import PurchaseOrdersPage from './pages/purchasing/PurchaseOrdersPage';
import SupplierQuotationsPage from './pages/purchasing/SupplierQuotationsPage';
import PurchaseRequisitionsPage from './pages/purchasing/PurchaseRequisitionsPage';
import VendorPricelistsPage from './pages/purchasing/VendorPricelistsPage';

// Phase 2: Inventory Pages
import StockPickingsPage from './pages/inventory/StockPickingsPage';
import InventoryQuantsPage from './pages/inventory/InventoryQuantsPage';
import InventoryAdjustmentsPage from './pages/inventory/InventoryAdjustmentsPage';

// Reports
import ReportsPage from './pages/reports/ReportsPage';

// Phase 3: Finance
import FinanceDashboardPage from './pages/finance/FinanceDashboardPage';
import FinanceJournalEntriesPage from './pages/finance/FinanceJournalEntriesPage';
import FinanceInvoicesPage from './pages/finance/FinanceInvoicesPage';
import FinancePaymentsPage from './pages/finance/FinancePaymentsPage';

// Phase 3: Notifications & Audit
import NotificationsPage from './pages/notifications/NotificationsPage';
import AuditLogPage from './pages/audit/AuditLogPage';

// Phase 4: Manufacturing
import ManufacturingDashboardPage from './pages/manufacturing/ManufacturingDashboardPage';
import ManufacturingOrdersPage from './pages/manufacturing/ManufacturingOrdersPage';
import BillOfMaterialsPage from './pages/manufacturing/BillOfMaterialsPage';
import WorkCentersPage from './pages/manufacturing/WorkCentersPage';
import QualityChecksPage from './pages/manufacturing/QualityChecksPage';
import EquipmentPage from './pages/manufacturing/EquipmentPage';
import MaintenanceOrdersPage from './pages/manufacturing/MaintenanceOrdersPage';
import AssetsPage from './pages/manufacturing/AssetsPage';
import RoutingsPage from './pages/manufacturing/RoutingsPage';
import ScrapOrdersPage from './pages/manufacturing/ScrapOrdersPage';
import AssetCategoriesPage from './pages/manufacturing/AssetCategoriesPage';

// Phase 5: HRM Pages
import HrDashboardPage from './pages/hrm/HrDashboardPage';
import EmployeesPage from './pages/hrm/EmployeesPage';
import EmployeeContractsPage from './pages/hrm/EmployeeContractsPage';
import EmployeeDocumentsPage from './pages/hrm/EmployeeDocumentsPage';
import WorkSchedulesPage from './pages/hrm/WorkSchedulesPage';
import ShiftsPage from './pages/hrm/ShiftsPage';
import AttendancePage from './pages/hrm/AttendancePage';
import AttendanceCorrectionsPage from './pages/hrm/AttendanceCorrectionsPage';
import LeaveTypesPage from './pages/hrm/LeaveTypesPage';
import LeaveBalancesPage from './pages/hrm/LeaveBalancesPage';
import LeaveRequestsPage from './pages/hrm/LeaveRequestsPage';
import PayrollComponentsPage from './pages/hrm/PayrollComponentsPage';
import SalaryStructuresPage from './pages/hrm/SalaryStructuresPage';
import EmployeeSalariesPage from './pages/hrm/EmployeeSalariesPage';
import PayrollPeriodsPage from './pages/hrm/PayrollPeriodsPage';
import PayrollRunsPage from './pages/hrm/PayrollRunsPage';
import PayslipsPage from './pages/hrm/PayslipsPage';
import JobVacanciesPage from './pages/hrm/JobVacanciesPage';
import ApplicantsPage from './pages/hrm/ApplicantsPage';
import InterviewsPage from './pages/hrm/InterviewsPage';
import ExpenseClaimsPage from './pages/hrm/ExpenseClaimsPage';

// Settings Pages
import SettingsUsersPage from './pages/settings/SettingsUsersPage';
import SettingsCompaniesPage from './pages/settings/SettingsCompaniesPage';
import SettingsBranchesPage from './pages/settings/SettingsBranchesPage';
import SettingsDepartmentsPage from './pages/settings/SettingsDepartmentsPage';
import SettingsPositionsPage from './pages/settings/SettingsPositionsPage';
import SettingsCurrenciesPage from './pages/settings/SettingsCurrenciesPage';
import SettingsTaxesPage from './pages/settings/SettingsTaxesPage';
import SettingsNumberingPage from './pages/settings/SettingsNumberingPage';

function ProtectedRoute() {
    const { user, loading } = useAuth();

    if (loading) return <PageLoader />;
    if (!user) return <Navigate to="/login" replace />;

    return (
        <DashboardLayout>
            <Outlet />
        </DashboardLayout>
    );
}

function GuestRoute() {
    const { user, loading } = useAuth();

    if (loading) return <PageLoader />;
    if (user) return <Navigate to="/" replace />;

    return <Outlet />;
}

export default function App() {
    return (
        <BrowserRouter>
            <AuthProvider>
                <Routes>
                    {/* Guest routes */}
                    <Route element={<GuestRoute />}>
                        <Route path="/login" element={<LoginPage />} />
                    </Route>

                    {/* Protected routes */}
                    <Route element={<ProtectedRoute />}>
                        <Route path="/" element={<DashboardPage />} />

                        {/* Master Data */}
                        <Route path="/master-data/product-categories" element={<ProductCategoriesPage />} />
                        <Route path="/master-data/unit-of-measures" element={<UnitOfMeasuresPage />} />
                        <Route path="/master-data/products" element={<ProductsPage />} />
                        <Route path="/master-data/customers" element={<CustomersPage />} />
                        <Route path="/master-data/vendors" element={<VendorsPage />} />
                        <Route path="/master-data/warehouses" element={<WarehousesPage />} />
                        <Route path="/master-data/stock-locations" element={<StockLocationsPage />} />
                        <Route path="/master-data/bank-accounts" element={<BankAccountsPage />} />
                        <Route path="/master-data/payment-methods" element={<PaymentMethodsPage />} />
                        <Route path="/master-data/chart-of-accounts" element={<ChartOfAccountsPage />} />

                        {/* Purchasing */}
                        <Route path="/purchasing/purchase-orders" element={<PurchaseOrdersPage />} />
                        <Route path="/purchasing/quotations" element={<SupplierQuotationsPage />} />
                        <Route path="/purchasing/requisitions" element={<PurchaseRequisitionsPage />} />
                        <Route path="/purchasing/pricelists" element={<VendorPricelistsPage />} />

                        {/* Inventory */}
                        <Route path="/inventory/quants" element={<InventoryQuantsPage />} />
                        <Route path="/inventory/pickings" element={<StockPickingsPage />} />
                        <Route path="/inventory/adjustments" element={<InventoryAdjustmentsPage />} />

                        {/* Finance */}
                        <Route path="/finance" element={<FinanceDashboardPage />} />
                        <Route path="/finance/journal-entries" element={<FinanceJournalEntriesPage />} />
                        <Route path="/finance/invoices" element={<FinanceInvoicesPage />} />
                        <Route path="/finance/payments" element={<FinancePaymentsPage />} />

                        {/* Reports */}
                        <Route path="/reports" element={<ReportsPage />} />

                        {/* Manufacturing */}
                        <Route path="/manufacturing" element={<ManufacturingDashboardPage />} />
                        <Route path="/manufacturing/orders" element={<ManufacturingOrdersPage />} />
                        <Route path="/manufacturing/boms" element={<BillOfMaterialsPage />} />
                        <Route path="/manufacturing/work-centers" element={<WorkCentersPage />} />
                        <Route path="/manufacturing/quality" element={<QualityChecksPage />} />
                        <Route path="/manufacturing/equipment" element={<EquipmentPage />} />
                        <Route path="/manufacturing/maintenance" element={<MaintenanceOrdersPage />} />
                        <Route path="/manufacturing/assets" element={<AssetsPage />} />
                        <Route path="/manufacturing/routings" element={<RoutingsPage />} />
                        <Route path="/manufacturing/scraps" element={<ScrapOrdersPage />} />
                        <Route path="/manufacturing/asset-categories" element={<AssetCategoriesPage />} />

                        {/* Notifications & Audit */}
                        <Route path="/notifications" element={<NotificationsPage />} />
                        <Route path="/audit-log" element={<AuditLogPage />} />

                        {/* HRM */}
                        <Route path="/hrm" element={<HrDashboardPage />} />
                        <Route path="/hrm/employees" element={<EmployeesPage />} />
                        <Route path="/hrm/contracts" element={<EmployeeContractsPage />} />
                        <Route path="/hrm/documents" element={<EmployeeDocumentsPage />} />
                        <Route path="/hrm/work-schedules" element={<WorkSchedulesPage />} />
                        <Route path="/hrm/shifts" element={<ShiftsPage />} />
                        <Route path="/hrm/attendance" element={<AttendancePage />} />
                        <Route path="/hrm/attendance-corrections" element={<AttendanceCorrectionsPage />} />
                        <Route path="/hrm/leave-types" element={<LeaveTypesPage />} />
                        <Route path="/hrm/leave-balances" element={<LeaveBalancesPage />} />
                        <Route path="/hrm/leave-requests" element={<LeaveRequestsPage />} />
                        <Route path="/hrm/payroll-components" element={<PayrollComponentsPage />} />
                        <Route path="/hrm/salary-structures" element={<SalaryStructuresPage />} />
                        <Route path="/hrm/employee-salaries" element={<EmployeeSalariesPage />} />
                        <Route path="/hrm/payroll-periods" element={<PayrollPeriodsPage />} />
                        <Route path="/hrm/payroll-runs" element={<PayrollRunsPage />} />
                        <Route path="/hrm/payslips" element={<PayslipsPage />} />
                        <Route path="/hrm/job-vacancies" element={<JobVacanciesPage />} />
                        <Route path="/hrm/applicants" element={<ApplicantsPage />} />
                        <Route path="/hrm/interviews" element={<InterviewsPage />} />
                        <Route path="/hrm/expense-claims" element={<ExpenseClaimsPage />} />

                        {/* Settings */}
                        <Route path="/settings/users" element={<SettingsUsersPage />} />
                        <Route path="/settings/companies" element={<SettingsCompaniesPage />} />
                        <Route path="/settings/branches" element={<SettingsBranchesPage />} />
                        <Route path="/settings/departments" element={<SettingsDepartmentsPage />} />
                        <Route path="/settings/positions" element={<SettingsPositionsPage />} />
                        <Route path="/settings/currencies" element={<SettingsCurrenciesPage />} />
                        <Route path="/settings/taxes" element={<SettingsTaxesPage />} />
                        <Route path="/settings/numbering" element={<SettingsNumberingPage />} />
                    </Route>

                    {/* Catch all */}
                    <Route path="*" element={<Navigate to="/" replace />} />
                </Routes>
            </AuthProvider>
        </BrowserRouter>
    );
}
