import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { reportsApi } from '../../api/endpoints';
import { Card, DataTable } from '../../components/ui';
import { Spinner } from '../../components/ui/Loader';
import { formatCurrency } from '../../components/ui/StatusBadge';
import {
    ShoppingCart, Package, TrendingUp, Users,
    BarChart3, AlertTriangle, ArrowRight,
    Warehouse, DollarSign, FileText
} from 'lucide-react';

const TABS = [
    { id: 'purchasing', label: 'Purchasing', icon: ShoppingCart },
    { id: 'inventory', label: 'Inventory', icon: Package },
];

export default function ReportsPage() {
    const [activeTab, setActiveTab] = useState('purchasing');
    const [poData, setPoData] = useState(null);
    const [invData, setInvData] = useState(null);
    const [poVendor, setPoVendor] = useState([]);
    const [poMonth, setPoMonth] = useState([]);
    const [poProducts, setPoProducts] = useState([]);
    const [invWarehouse, setInvWarehouse] = useState([]);
    const [invLowStock, setInvLowStock] = useState([]);
    const [loading, setLoading] = useState(true);
    const [subLoading, setSubLoading] = useState({});
    const navigate = useNavigate();

    useEffect(() => { loadSummary(); }, []);

    const loadSummary = async () => {
        setLoading(true);
        try {
            const [poRes, invRes] = await Promise.all([
                reportsApi.purchasing({ type: 'summary' }),
                reportsApi.inventory({ type: 'summary' }),
            ]);
            setPoData(poRes.data.data);
            setInvData(invRes.data.data);
        } catch (err) {
            console.error('Failed to load reports:', err);
        } finally {
            setLoading(false);
        }
    };

    const loadSubReport = async (key, type, query) => {
        setSubLoading(prev => ({ ...prev, [key]: true }));
        try {
            const res = await reportsApi[type]({ type: query });
            return res.data.data;
        } catch (err) {
            console.error(`Failed to load ${key}:`, err);
            return [];
        } finally {
            setSubLoading(prev => ({ ...prev, [key]: false }));
        }
    };

    const loadPurchasingDetails = async () => {
        const [vendors, months, products] = await Promise.all([
            loadSubReport('poVendor', 'purchasing', 'by_vendor'),
            loadSubReport('poMonth', 'purchasing', 'by_month'),
            loadSubReport('poProducts', 'purchasing', 'top_products'),
        ]);
        setPoVendor(vendors);
        setPoMonth(months);
        setPoProducts(products);
    };

    const loadInventoryDetails = async () => {
        const [warehouses, lowStock] = await Promise.all([
            loadSubReport('invWarehouse', 'inventory', 'by_warehouse'),
            loadSubReport('invLowStock', 'inventory', 'low_stock'),
        ]);
        setInvWarehouse(warehouses);
        setInvLowStock(lowStock);
    };

    useEffect(() => {
        if (activeTab === 'purchasing' && poData && poVendor.length === 0) loadPurchasingDetails();
        if (activeTab === 'inventory' && invData && invWarehouse.length === 0) loadInventoryDetails();
    }, [activeTab, poData, invData]);

    if (loading) {
        return (
            <div className="flex items-center justify-center min-h-[60vh]">
                <div className="text-center">
                    <Spinner size="lg" />
                    <p className="mt-3 text-sm text-gray-500">Loading reports...</p>
                </div>
            </div>
        );
    }

    const poTotals = poData?.totals || {};
    const poStatus = poData?.by_status || [];
    const poTopVendors = poData?.top_vendors || [];
    const invTotals = invData?.totals || {};
    const invByType = invData?.by_type || [];
    const invTopLocations = invData?.top_locations || [];

    return (
        <div className="space-y-6">
            {/* Header */}
            <div>
                <h1 className="text-2xl font-bold text-gray-900">Reports</h1>
                <p className="text-sm text-gray-500">Business intelligence and data analytics</p>
            </div>

            {/* Tab Navigation */}
            <div className="border-b border-gray-200">
                <nav className="flex gap-6">
                    {TABS.map((tab) => (
                        <button
                            key={tab.id}
                            onClick={() => setActiveTab(tab.id)}
                            className={`flex items-center gap-2 pb-3 text-sm font-medium border-b-2 transition-colors ${
                                activeTab === tab.id
                                    ? 'border-indigo-600 text-indigo-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                            }`}
                        >
                            <tab.icon className="h-4 w-4" />
                            {tab.label}
                        </button>
                    ))}
                </nav>
            </div>

            {/* ── PURCHASING TAB ──────────────────────── */}
            {activeTab === 'purchasing' && (
                <div className="space-y-6">
                    {/* Summary Stats */}
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div className="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl p-5 text-white">
                            <div className="flex items-center gap-3">
                                <ShoppingCart className="h-8 w-8 opacity-80" />
                                <div>
                                    <p className="text-sm opacity-80">Total Orders</p>
                                    <p className="text-2xl font-bold">{poTotals.total_orders || 0}</p>
                                </div>
                            </div>
                        </div>
                        <div className="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-5 text-white">
                            <div className="flex items-center gap-3">
                                <DollarSign className="h-8 w-8 opacity-80" />
                                <div>
                                    <p className="text-sm opacity-80">Total Value</p>
                                    <p className="text-2xl font-bold">{formatCurrency(poTotals.total_value || 0)}</p>
                                </div>
                            </div>
                        </div>
                        <div className="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-5 text-white">
                            <div className="flex items-center gap-3">
                                <Users className="h-8 w-8 opacity-80" />
                                <div>
                                    <p className="text-sm opacity-80">Unique Vendors</p>
                                    <p className="text-2xl font-bold">{poTotals.unique_vendors || 0}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* PO by Status + Top Vendors */}
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <Card title="Orders by Status" subtitle="Current purchasing pipeline">
                            <div className="space-y-3">
                                {poStatus.length > 0 ? poStatus.map((item, i) => {
                                    const colors = {
                                        draft: 'bg-gray-400',
                                        waiting_approval: 'bg-yellow-400',
                                        approved: 'bg-blue-400',
                                        ordered: 'bg-indigo-400',
                                        partial_received: 'bg-orange-400',
                                        received: 'bg-green-400',
                                        cancelled: 'bg-red-400',
                                    };
                                    const pct = poTotals.total_orders ? Math.round((item.count / poTotals.total_orders) * 100) : 0;
                                    return (
                                        <div key={i} className="flex items-center gap-3">
                                            <span className="w-28 text-xs capitalize text-gray-600">{item.status.replace('_', ' ')}</span>
                                            <div className="flex-1 bg-gray-100 rounded-full h-4 overflow-hidden">
                                                <div className={`h-full rounded-full ${colors[item.status] || 'bg-gray-400'}`} style={{ width: `${pct}%` }} />
                                            </div>
                                            <span className="w-8 text-right text-xs font-medium text-gray-700">{item.count}</span>
                                            <span className="w-24 text-right text-xs text-gray-500">{formatCurrency(item.total_value)}</span>
                                        </div>
                                    );
                                }) : <p className="text-sm text-gray-500">No purchase orders yet</p>}
                            </div>
                        </Card>

                        <Card title="Top Vendors" subtitle="By total spend">
                            <div className="space-y-3">
                                {poTopVendors.length > 0 ? poTopVendors.map((v, i) => (
                                    <div key={i} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div className="flex items-center gap-3">
                                            <div className={`w-8 h-8 rounded-full flex items-center justify-center text-white font-bold text-xs ${
                                                ['bg-indigo-500', 'bg-green-500', 'bg-blue-500', 'bg-orange-500', 'bg-purple-500'][i % 5]
                                            }`}>
                                                {i + 1}
                                            </div>
                                            <div>
                                                <p className="text-sm font-medium text-gray-900">{v.vendor_name}</p>
                                                <p className="text-xs text-gray-500">{v.orders} order{v.orders > 1 ? 's' : ''}</p>
                                            </div>
                                        </div>
                                        <span className="text-sm font-semibold text-gray-900">{formatCurrency(v.total_spent)}</span>
                                    </div>
                                )) : <p className="text-sm text-gray-500">No vendor data</p>}
                            </div>
                        </Card>
                    </div>

                    {/* Monthly Trend */}
                    <Card title="Monthly Trend" subtitle={`${new Date().getFullYear()} purchasing activity`}>
                        {subLoading.poMonth ? (
                            <div className="flex justify-center py-8"><Spinner /></div>
                        ) : poMonth.length > 0 ? (
                            <div className="grid grid-cols-12 gap-2">
                                {poMonth.map((m, i) => {
                                    const maxVal = Math.max(...poMonth.map(x => x.total_value || 1));
                                    const pct = maxVal ? Math.round((m.total_value / maxVal) * 100) : 0;
                                    return (
                                        <div key={i} className="flex flex-col items-center gap-1">
                                            <span className="text-xs font-medium text-gray-700">{formatCurrency(m.total_value)}</span>
                                            <div className="w-full bg-gray-100 rounded-t-md" style={{ height: '120px', display: 'flex', alignItems: 'flex-end' }}>
                                                <div className="w-full bg-indigo-500 rounded-t-md transition-all" style={{ height: `${Math.max(pct, 4)}%` }} />
                                            </div>
                                            <span className="text-xs text-gray-500">{m.month_name}</span>
                                            <span className="text-xs text-gray-400">{m.orders} PO</span>
                                        </div>
                                    );
                                })}
                            </div>
                        ) : (
                            <p className="text-sm text-gray-500 text-center py-8">No monthly data for this year</p>
                        )}
                    </Card>

                    {/* Top Products Purchased */}
                    <Card title="Top Purchased Products" subtitle="Most ordered products by value">
                        {subLoading.poProducts ? (
                            <div className="flex justify-center py-8"><Spinner /></div>
                        ) : (
                            <DataTable
                                columns={[
                                    { key: 'product_code', label: 'Code', className: 'font-mono text-xs' },
                                    { key: 'product_name', label: 'Product' },
                                    { key: 'total_qty', label: 'Total Qty', render: (r) => Number(r.total_qty).toLocaleString('id-ID') },
                                    { key: 'total_amount', label: 'Total Value', render: (r) => formatCurrency(r.total_amount) },
                                    { key: 'order_count', label: 'Orders' },
                                ]}
                                data={poProducts}
                                emptyMessage="No purchase line data"
                            />
                        )}
                    </Card>
                </div>
            )}

            {/* ── INVENTORY TAB ───────────────────────── */}
            {activeTab === 'inventory' && (
                <div className="space-y-6">
                    {/* Summary Stats */}
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-4">
                        <div className="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl p-5 text-white">
                            <div className="flex items-center gap-3">
                                <DollarSign className="h-8 w-8 opacity-80" />
                                <div>
                                    <p className="text-sm opacity-80">Stock Value</p>
                                    <p className="text-xl font-bold">{formatCurrency(invTotals.total_value || 0)}</p>
                                </div>
                            </div>
                        </div>
                        <div className="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-5 text-white">
                            <div className="flex items-center gap-3">
                                <Package className="h-8 w-8 opacity-80" />
                                <div>
                                    <p className="text-sm opacity-80">Total Quantity</p>
                                    <p className="text-xl font-bold">{Number(invTotals.total_quantity || 0).toLocaleString('id-ID')}</p>
                                </div>
                            </div>
                        </div>
                        <div className="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-5 text-white">
                            <div className="flex items-center gap-3">
                                <BarChart3 className="h-8 w-8 opacity-80" />
                                <div>
                                    <p className="text-sm opacity-80">Products</p>
                                    <p className="text-xl font-bold">{invTotals.total_products || 0}</p>
                                </div>
                            </div>
                        </div>
                        <div className={`bg-gradient-to-br rounded-xl p-5 text-white ${
                            invTotals.low_stock_count > 0 ? 'from-red-500 to-red-600' : 'from-emerald-500 to-emerald-600'
                        }`}>
                            <div className="flex items-center gap-3">
                                <AlertTriangle className="h-8 w-8 opacity-80" />
                                <div>
                                    <p className="text-sm opacity-80">Low Stock</p>
                                    <p className="text-xl font-bold">{invTotals.low_stock_count || 0}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Top Locations + Product Types */}
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <Card title="Stock by Location" subtitle="Top locations by value">
                            <div className="space-y-3">
                                {invTopLocations.length > 0 ? invTopLocations.map((loc, i) => {
                                    const maxVal = Math.max(...invTopLocations.map(x => x.value || 1));
                                    const pct = maxVal ? Math.round((loc.value / maxVal) * 100) : 0;
                                    return (
                                        <div key={i} className="space-y-1">
                                            <div className="flex justify-between items-center">
                                                <span className="text-sm font-medium text-gray-900">{loc.location_name || `Location #${loc.location_id}`}</span>
                                                <span className="text-sm font-semibold text-indigo-600">{formatCurrency(loc.value)}</span>
                                            </div>
                                            <div className="flex-1 bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                                <div className="h-full bg-indigo-500 rounded-full" style={{ width: `${pct}%` }} />
                                            </div>
                                            <span className="text-xs text-gray-500">{Number(loc.quantity).toLocaleString('id-ID')} units</span>
                                        </div>
                                    );
                                }) : <p className="text-sm text-gray-500">No stock location data</p>}
                            </div>
                        </Card>

                        <Card title="Products by Type" subtitle="Product distribution">
                            <div className="space-y-3">
                                {invByType.length > 0 ? invByType.map((item, i) => {
                                    const colors = { storable: 'bg-blue-500', consumable: 'bg-orange-500', service: 'bg-purple-500' };
                                    return (
                                        <div key={i} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <div className="flex items-center gap-3">
                                                <div className={`w-3 h-3 rounded-full ${colors[item.type] || 'bg-gray-400'}`} />
                                                <span className="text-sm font-medium capitalize text-gray-900">{item.type}</span>
                                            </div>
                                            <span className="text-sm font-bold text-gray-700">{item.count}</span>
                                        </div>
                                    );
                                }) : <p className="text-sm text-gray-500">No product type data</p>}
                            </div>
                        </Card>
                    </div>

                    {/* Stock Value by Warehouse */}
                    <Card title="Stock Value by Location" subtitle="Detailed breakdown">
                        {subLoading.invWarehouse ? (
                            <div className="flex justify-center py-8"><Spinner /></div>
                        ) : (
                            <DataTable
                                columns={[
                                    { key: 'location_name', label: 'Location' },
                                    { key: 'product_count', label: 'Products', className: 'text-center' },
                                    { key: 'total_qty', label: 'Total Qty', render: (r) => Number(r.total_qty).toLocaleString('id-ID') },
                                    { key: 'reserved_qty', label: 'Reserved', render: (r) => Number(r.reserved_qty).toLocaleString('id-ID') },
                                    { key: 'total_value', label: 'Value', render: (r) => formatCurrency(r.total_value) },
                                ]}
                                data={invWarehouse}
                                emptyMessage="No warehouse stock data"
                            />
                        )}
                    </Card>

                    {/* Low Stock Products */}
                    <Card
                        title="Low Stock Products"
                        subtitle={invLowStock.length > 0 ? `${invLowStock.length} products need restocking` : 'All stock levels healthy'}
                        actions={
                            invLowStock.length > 0 ? (
                                <button
                                    onClick={() => navigate('/inventory/quants')}
                                    className="text-sm text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1"
                                >
                                    View Stock <ArrowRight className="h-3 w-3" />
                                </button>
                            ) : null
                        }
                    >
                        {subLoading.invLowStock ? (
                            <div className="flex justify-center py-8"><Spinner /></div>
                        ) : (
                            <DataTable
                                columns={[
                                    { key: 'code', label: 'Code', className: 'font-mono text-xs' },
                                    { key: 'name', label: 'Product' },
                                    { key: 'on_hand', label: 'On Hand', render: (r) => Number(r.on_hand).toLocaleString('id-ID') },
                                    { key: 'minimum_stock', label: 'Min Stock', render: (r) => Number(r.minimum_stock).toLocaleString('id-ID') },
                                    { key: 'deficit', label: 'Deficit', render: (r) => (
                                        <span className="text-red-600 font-semibold">-{Number(r.deficit).toLocaleString('id-ID')}</span>
                                    )},
                                    { key: 'restock_value', label: 'Restock Value', render: (r) => formatCurrency(r.restock_value) },
                                ]}
                                data={invLowStock}
                                emptyMessage="All products are above minimum stock levels"
                            />
                        )}
                    </Card>
                </div>
            )}
        </div>
    );
}
