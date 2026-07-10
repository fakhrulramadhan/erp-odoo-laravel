import { NavLink } from 'react-router-dom';
import {
    LayoutDashboard, Users, Building2, Settings,
    FileText, Bell, X, ChevronDown,
    Landmark, MapPin, Briefcase, DollarSign,
    Hash, Receipt, Database, Package, Ruler,
    Boxes, Truck, Warehouse, CreditCard, BookOpen,
    ShoppingCart, ClipboardList, BarChart3, PackageCheck, ClipboardCheck,
    Factory, Wrench, ArrowRightLeft, AlertTriangle,
    UserCheck, Clock, CalendarDays, Banknote, UserPlus, Contact, FileCheck, Timer, ScrollText
} from 'lucide-react';
import Logo from '../ui/Logo';
import { useState } from 'react';

const navigation = [
    { name: 'Dashboard', to: '/', icon: LayoutDashboard },
    {
        name: 'Master Data',
        icon: Database,
        children: [
            { name: 'Product Categories', to: '/master-data/product-categories', icon: Package },
            { name: 'Unit of Measures', to: '/master-data/unit-of-measures', icon: Ruler },
            { name: 'Products', to: '/master-data/products', icon: Boxes },
            { name: 'Customers', to: '/master-data/customers', icon: Users },
            { name: 'Vendors', to: '/master-data/vendors', icon: Truck },
            { name: 'Warehouses', to: '/master-data/warehouses', icon: Warehouse },
            { name: 'Stock Locations', to: '/master-data/stock-locations', icon: MapPin },
            { name: 'Bank Accounts', to: '/master-data/bank-accounts', icon: Landmark },
            { name: 'Payment Methods', to: '/master-data/payment-methods', icon: CreditCard },
            { name: 'Chart of Accounts', to: '/master-data/chart-of-accounts', icon: BookOpen },
        ],
    },
    {
        name: 'Purchasing',
        icon: ShoppingCart,
        children: [
            { name: 'Purchase Orders', to: '/purchasing/purchase-orders', icon: FileText },
            { name: 'Supplier Quotations', to: '/purchasing/quotations', icon: ClipboardList },
            { name: 'Purchase Requisitions', to: '/purchasing/requisitions', icon: ClipboardCheck },
            { name: 'Vendor Pricelists', to: '/purchasing/pricelists', icon: DollarSign },
        ],
    },
    {
        name: 'Inventory',
        icon: Boxes,
        children: [
            { name: 'Stock Overview', to: '/inventory/quants', icon: Package },
            { name: 'Stock Pickings', to: '/inventory/pickings', icon: Truck },
            { name: 'Adjustments', to: '/inventory/adjustments', icon: ClipboardList },
        ],
    },
    {
        name: 'Finance',
        icon: DollarSign,
        children: [
            { name: 'Finance Dashboard', to: '/finance', icon: LayoutDashboard },
            { name: 'Journal Entries', to: '/finance/journal-entries', icon: BookOpen },
            { name: 'Invoices', to: '/finance/invoices', icon: Receipt },
            { name: 'Payments', to: '/finance/payments', icon: CreditCard },
        ],
    },
    {
        name: 'Manufacturing',
        icon: Factory,
        children: [
            { name: 'Mfg Dashboard', to: '/manufacturing', icon: LayoutDashboard },
            { name: 'Manufacturing Orders', to: '/manufacturing/orders', icon: FileText },
            { name: 'Bill of Materials', to: '/manufacturing/boms', icon: ClipboardList },
            { name: 'Work Centers', to: '/manufacturing/work-centers', icon: Settings },
            { name: 'Routings', to: '/manufacturing/routings', icon: ArrowRightLeft },
            { name: 'Quality Checks', to: '/manufacturing/quality', icon: ClipboardCheck },
            { name: 'Scrap Orders', to: '/manufacturing/scraps', icon: AlertTriangle },
            { name: 'Equipment', to: '/manufacturing/equipment', icon: Wrench },
            { name: 'Maintenance', to: '/manufacturing/maintenance', icon: Wrench },
            { name: 'Asset Categories', to: '/manufacturing/asset-categories', icon: Package },
            { name: 'Fixed Assets', to: '/manufacturing/assets', icon: Package },
        ],
    },
    { name: 'Reports', to: '/reports', icon: BarChart3 },
    {
        name: 'Settings',
        icon: Settings,
        children: [
            { name: 'Users', to: '/settings/users', icon: Users },
            { name: 'Companies', to: '/settings/companies', icon: Building2 },
            { name: 'Branches', to: '/settings/branches', icon: MapPin },
            { name: 'Departments', to: '/settings/departments', icon: Landmark },
            { name: 'Positions', to: '/settings/positions', icon: Briefcase },
            { name: 'Currencies', to: '/settings/currencies', icon: DollarSign },
            { name: 'Tax Settings', to: '/settings/taxes', icon: Receipt },
            { name: 'Numbering', to: '/settings/numbering', icon: Hash },
        ],
    },
    { name: 'Audit Log', to: '/audit-log', icon: FileText },
    { name: 'Notifications', to: '/notifications', icon: Bell },
];

const hrmNavigation = [
    {
        name: 'HRM',
        icon: UserCheck,
        children: [
            { name: 'HR Dashboard', to: '/hrm', icon: LayoutDashboard },
            { name: 'Employees', to: '/hrm/employees', icon: Users },
            { name: 'Contracts', to: '/hrm/contracts', icon: FileText },
            { name: 'Documents', to: '/hrm/documents', icon: FileCheck },
            { name: 'Work Schedules', to: '/hrm/work-schedules', icon: Timer },
            { name: 'Shifts', to: '/hrm/shifts', icon: Clock },
            { name: 'Attendance', to: '/hrm/attendance', icon: Clock },
            { name: 'Corrections', to: '/hrm/attendance-corrections', icon: ClipboardCheck },
            { name: 'Leave Types', to: '/hrm/leave-types', icon: CalendarDays },
            { name: 'Leave Balances', to: '/hrm/leave-balances', icon: ScrollText },
            { name: 'Leave Requests', to: '/hrm/leave-requests', icon: CalendarDays },
            { name: 'Payroll Components', to: '/hrm/payroll-components', icon: DollarSign },
            { name: 'Salary Structures', to: '/hrm/salary-structures', icon: Banknote },
            { name: 'Employee Salaries', to: '/hrm/employee-salaries', icon: DollarSign },
            { name: 'Payroll Periods', to: '/hrm/payroll-periods', icon: CalendarDays },
            { name: 'Payroll Runs', to: '/hrm/payroll-runs', icon: BarChart3 },
            { name: 'Payslips', to: '/hrm/payslips', icon: Receipt },
            { name: 'Job Vacancies', to: '/hrm/job-vacancies', icon: Briefcase },
            { name: 'Applicants', to: '/hrm/applicants', icon: UserPlus },
            { name: 'Interviews', to: '/hrm/interviews', icon: Contact },
            { name: 'Expense Claims', to: '/hrm/expense-claims', icon: Receipt },
        ],
    },
];

function NavItem({ item }) {
    const [open, setOpen] = useState(false);
    const Icon = item.icon;

    if (item.children) {
        return (
            <div>
                <button
                    onClick={() => setOpen(!open)}
                    className="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-300 hover:bg-gray-800 hover:text-white transition-colors"
                >
                    <Icon className="h-5 w-5" />
                    <span className="flex-1 text-left">{item.name}</span>
                    <ChevronDown className={`h-4 w-4 transition-transform ${open ? 'rotate-180' : ''}`} />
                </button>
                {open && (
                    <div className="ml-4 mt-1 space-y-1 border-l border-gray-700 pl-3">
                        {item.children.map((child) => (
                            <NavLink
                                key={child.to}
                                to={child.to}
                                className={({ isActive }) =>
                                    `flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm transition-colors ${
                                        isActive ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:text-white'
                                    }`
                                }
                            >
                                <child.icon className="h-4 w-4" />
                                {child.name}
                            </NavLink>
                        ))}
                    </div>
                )}
            </div>
        );
    }

    return (
        <NavLink
            to={item.to}
            end={item.to === '/'}
            className={({ isActive }) =>
                `flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors ${
                    isActive ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                }`
            }
        >
            <Icon className="h-5 w-5" />
            {item.name}
        </NavLink>
    );
}

export default function Sidebar({ open, onClose }) {
    return (
        <>
            {/* Mobile overlay */}
            {open && (
                <div className="fixed inset-0 z-40 bg-black/50 lg:hidden" onClick={onClose} />
            )}

            <aside
                className={`fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-gray-900 transition-transform duration-300 lg:static lg:translate-x-0 ${
                    open ? 'translate-x-0' : '-translate-x-full'
                }`}
            >
                {/* Logo */}
                <div className="flex h-16 items-center justify-between border-b border-gray-800 px-4">
                    <Logo size="sm" />
                    <button onClick={onClose} className="text-gray-400 hover:text-white lg:hidden">
                        <X className="h-5 w-5" />
                    </button>
                </div>

                {/* Navigation */}
                <nav className="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                    {[...navigation, ...hrmNavigation].map((item) => (
                        <NavItem key={item.name} item={item} />
                    ))}
                </nav>

                {/* Footer */}
                <div className="border-t border-gray-800 px-4 py-3">
                    <p className="text-xs text-gray-500">FLTech ERP v1.0.0</p>
                </div>
            </aside>
        </>
    );
}
