import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { dashboardApi } from '../../api/endpoints';
import { StatCard, Card, DataTable } from '../../components/ui';
import { Spinner } from '../../components/ui/Loader';
import { formatCurrency } from '../../components/ui/StatusBadge';
import {
    Users, Building2, Bell, Activity,
    ShoppingCart, ClipboardList, ClipboardCheck,
    Package, Truck, AlertTriangle,
    ArrowRight, DollarSign, Boxes
} from 'lucide-react';

export default function DashboardPage() {
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const navigate = useNavigate();

    useEffect(() => { loadDashboard(); }, []);

    const loadDashboard = async () => {
        try {
            const res = await dashboardApi.index();
            setData(res.data.data);
        } catch (err) {
            console.error('Failed to load dashboard:', err);
        } finally {
            setLoading(false);
        }
    };

    if (loading) {
        return (
            <div className="flex items-center justify-center min-h-[60vh]">
                <div className="text-center">
                    <Spinner size="lg" />
                    <p className="mt-3 text-sm text-gray-500">Loading dashboard...</p>
                </div>
            </div>
        );
    }

    const stats = data?.stats || {};
    const po = data?.purchasing?.purchase_orders || {};
    const sq = data?.purchasing?.quotations || {};
    const pr = data?.purchasing?.requisitions || {};
    const inv = data?.inventory || {};
    const lowStock = inv.low_stock_products || [];
    const pickings = inv.pickings || {};

    const activityColumns = [
        { key: 'user', label: 'User' },
        {
            key: 'event', label: 'Event',
            render: (row) => {
                const colors = { created: 'text-green-600', updated: 'text-blue-600', deleted: 'text-red-600' };
                return <span className={`capitalize font-medium ${colors[row.event] || 'text-gray-600'}`}>{row.event}</span>;
            }
        },
        { key: 'model', label: 'Model' },
        { key: 'created_at', label: 'Time' },
    ];

    return (
        <div className="space-y-6">
            {/* Header */}
            <div>
                <h1 className="text-2xl font-bold text-gray-900">Dashboard</h1>
                <p className="text-sm text-gray-500">Welcome back! Here's what's happening across your ERP.</p>
            </div>

            {/* ── Row 1: System Stats ──────────────────── */}
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard label="Total Users" value={stats.total_users ?? '—'} icon={Users} color="indigo" />
                <StatCard label="Active Users" value={stats.active_users ?? '—'} icon={Activity} color="green" />
                <StatCard label="Companies" value={stats.total_companies ?? '—'} icon={Building2} color="blue" />
                <StatCard label="Notifications" value={stats.unread_notifications ?? '—'} icon={Bell} color="yellow" />
            </div>

            {/* ── Row 2: Purchasing Overview ───────────── */}
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard label="Purchase Orders" value={po.total ?? 0} icon={ShoppingCart} color="indigo" />
                <StatCard label="Quotations" value={sq.total ?? 0} icon={ClipboardList} color="blue" />
                <StatCard label="Requisitions" value={pr.total ?? 0} icon={ClipboardCheck} color="green" />
                <StatCard
                    label="PO Total Value"
                    value={formatCurrency(po.total_value || 0)}
                    icon={DollarSign}
                    color="yellow"
                />
            </div>

            {/* ── Row 3: Inventory + PO Status Cards ───── */}
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
                {/* Inventory Summary */}
                <Card title="Inventory Overview" subtitle="Stock summary">
                    <div className="space-y-4">
                        <div className="flex items-center justify-between">
                            <span className="text-sm text-gray-600">Total Stock Value</span>
                            <span className="text-lg font-bold text-indigo-600">{formatCurrency(inv.total_value || 0)}</span>
                        </div>
                        <div className="flex items-center justify-between">
                            <span className="text-sm text-gray-600">Total On Hand</span>
                            <span className="text-lg font-bold text-gray-900">{Number(inv.total_on_hand || 0).toLocaleString('id-ID')}</span>
                        </div>
                        <div className="flex items-center justify-between">
                            <span className="text-sm text-gray-600">Pending Pickings</span>
                            <span className="text-lg font-bold text-orange-600">{pickings.draft || 0}</span>
                        </div>
                        <button
                            onClick={() => navigate('/inventory/quants')}
                            className="w-full mt-2 flex items-center justify-center gap-2 text-sm text-indigo-600 hover:text-indigo-800 font-medium"
                        >
                            View Stock Overview <ArrowRight className="h-4 w-4" />
                        </button>
                    </div>
                </Card>

                {/* PO Status Breakdown */}
                <Card title="Purchase Order Status" subtitle="By workflow stage">
                    <div className="space-y-3">
                        {[
                            { label: 'Draft', value: po.draft, color: 'bg-gray-100 text-gray-700' },
                            { label: 'Waiting Approval', value: po.waiting_approval, color: 'bg-yellow-100 text-yellow-700' },
                            { label: 'Approved', value: po.approved, color: 'bg-blue-100 text-blue-700' },
                            { label: 'Active (Ordered/Receiving)', value: po.active, color: 'bg-green-100 text-green-700' },
                        ].map((item, i) => (
                            <div key={i} className="flex items-center justify-between">
                                <span className="text-sm text-gray-600">{item.label}</span>
                                <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ${item.color}`}>
                                    {item.value || 0}
                                </span>
                            </div>
                        ))}
                        <button
                            onClick={() => navigate('/purchasing/purchase-orders')}
                            className="w-full mt-2 flex items-center justify-center gap-2 text-sm text-indigo-600 hover:text-indigo-800 font-medium"
                        >
                            Manage Purchase Orders <ArrowRight className="h-4 w-4" />
                        </button>
                    </div>
                </Card>

                {/* Quotation & Requisition Status */}
                <Card title="Quotations & Requisitions" subtitle="Purchasing pipeline">
                    <div className="space-y-4">
                        <div>
                            <p className="text-xs uppercase tracking-wider text-gray-400 font-medium mb-2">Supplier Quotations</p>
                            <div className="grid grid-cols-2 gap-2">
                                {[
                                    { label: 'Draft', value: sq.draft },
                                    { label: 'Sent', value: sq.sent },
                                    { label: 'Received', value: sq.received },
                                    { label: 'Accepted', value: sq.accepted },
                                ].map((item, i) => (
                                    <div key={i} className="bg-gray-50 rounded-lg px-3 py-2 text-center">
                                        <p className="text-lg font-bold text-gray-900">{item.value || 0}</p>
                                        <p className="text-xs text-gray-500">{item.label}</p>
                                    </div>
                                ))}
                            </div>
                        </div>
                        <div className="border-t border-gray-100 pt-3">
                            <p className="text-xs uppercase tracking-wider text-gray-400 font-medium mb-2">Purchase Requisitions</p>
                            <div className="grid grid-cols-3 gap-2">
                                {[
                                    { label: 'Draft', value: pr.draft },
                                    { label: 'Submitted', value: pr.submitted },
                                    { label: 'Approved', value: pr.approved },
                                ].map((item, i) => (
                                    <div key={i} className="bg-gray-50 rounded-lg px-3 py-2 text-center">
                                        <p className="text-lg font-bold text-gray-900">{item.value || 0}</p>
                                        <p className="text-xs text-gray-500">{item.label}</p>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </Card>
            </div>

            {/* ── Row 4: Low Stock Alerts + Recent Activity ── */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                {/* Low Stock Alerts */}
                <Card
                    title="Low Stock Alerts"
                    subtitle={lowStock.length > 0 ? `${lowStock.length} products below minimum` : 'All stock levels healthy'}
                    actions={
                        lowStock.length > 0 ? (
                            <button
                                onClick={() => navigate('/inventory/quants')}
                                className="text-sm text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1"
                            >
                                View All <ArrowRight className="h-3 w-3" />
                            </button>
                        ) : null
                    }
                >
                    {lowStock.length > 0 ? (
                        <div className="space-y-2">
                            {lowStock.map((item) => (
                                <div key={item.id} className="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-100">
                                    <div className="flex items-center gap-3">
                                        <div className="p-2 bg-red-100 rounded-lg">
                                            <AlertTriangle className="h-4 w-4 text-red-600" />
                                        </div>
                                        <div>
                                            <p className="text-sm font-medium text-gray-900">{item.name}</p>
                                            <p className="text-xs text-gray-500">{item.code}</p>
                                        </div>
                                    </div>
                                    <div className="text-right">
                                        <p className="text-sm font-semibold text-red-600">
                                            {item.on_hand} / {item.minimum_stock}
                                        </p>
                                        <p className="text-xs text-red-500">-{item.deficit} deficit</p>
                                    </div>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <div className="flex flex-col items-center justify-center py-8 text-gray-400">
                            <Package className="h-10 w-10 mb-2" />
                            <p className="text-sm">All products are above minimum stock levels</p>
                        </div>
                    )}
                </Card>

                {/* Recent Activity */}
                <Card title="Recent Activity" subtitle="Latest system activities">
                    <DataTable
                        columns={activityColumns}
                        data={data?.recent_activities ?? []}
                        emptyMessage="No recent activity"
                    />
                </Card>
            </div>

            {/* ── Row 5: Quick Actions ─────────────────── */}
            <Card title="Quick Actions" subtitle="Common tasks">
                <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    {[
                        { label: 'New Purchase Order', icon: ShoppingCart, to: '/purchasing/purchase-orders', color: 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border-indigo-200' },
                        { label: 'New Requisition', icon: ClipboardCheck, to: '/purchasing/requisitions', color: 'bg-green-50 text-green-700 hover:bg-green-100 border-green-200' },
                        { label: 'Stock Pickings', icon: Truck, to: '/inventory/pickings', color: 'bg-orange-50 text-orange-700 hover:bg-orange-100 border-orange-200' },
                        { label: 'Inventory Adjust', icon: ClipboardList, to: '/inventory/adjustments', color: 'bg-purple-50 text-purple-700 hover:bg-purple-100 border-purple-200' },
                    ].map((action, i) => (
                        <button
                            key={i}
                            onClick={() => navigate(action.to)}
                            className={`flex flex-col items-center gap-2 p-4 rounded-xl border transition-colors ${action.color}`}
                        >
                            <action.icon className="h-6 w-6" />
                            <span className="text-xs font-medium text-center">{action.label}</span>
                        </button>
                    ))}
                </div>
            </Card>
        </div>
    );
}
