import { useState, useEffect, useCallback } from 'react';
import { dashboardApi } from '../../api/endpoints';

const eventColors = {
    created: 'bg-green-100 text-green-700',
    updated: 'bg-blue-100 text-blue-700',
    deleted: 'bg-red-100 text-red-700',
    viewed: 'bg-gray-100 text-gray-600',
    login: 'bg-purple-100 text-purple-700',
    logout: 'bg-orange-100 text-orange-700',
    approved: 'bg-emerald-100 text-emerald-700',
    submitted: 'bg-indigo-100 text-indigo-700',
    cancelled: 'bg-red-100 text-red-700',
};

export default function AuditLogPage() {
    const [logs, setLogs] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [pagination, setPagination] = useState({ current_page: 1, last_page: 1, total: 0 });
    const [filters, setFilters] = useState({
        event: '',
        auditable_type: '',
        user_id: '',
        date_from: '',
        date_to: '',
    });

    const fetchLogs = useCallback(async (page = 1) => {
        setLoading(true);
        try {
            const params = { page, per_page: 20 };
            Object.entries(filters).forEach(([k, v]) => { if (v) params[k] = v; });
            const res = await dashboardApi.auditLogs(params);
            const data = res.data?.data;
            if (data?.data) {
                setLogs(data.data);
                setPagination({
                    current_page: data.current_page || 1,
                    last_page: data.last_page || 1,
                    total: data.total || 0,
                });
            } else if (Array.isArray(data)) {
                setLogs(data);
            }
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to load audit logs');
        } finally {
            setLoading(false);
        }
    }, [filters]);

    useEffect(() => { fetchLogs(1); }, [fetchLogs]);

    const handleFilterChange = (key, value) => {
        setFilters(prev => ({ ...prev, [key]: value }));
    };

    const clearFilters = () => {
        setFilters({ event: '', auditable_type: '', user_id: '', date_from: '', date_to: '' });
    };

    const hasActiveFilters = Object.values(filters).some(v => v);

    const formatModelName = (type) => {
        if (!type) return '-';
        const parts = type.split('\\');
        return parts[parts.length - 1];
    };

    return (
        <div className="space-y-6">
            {/* Header */}
            <div>
                <h1 className="text-2xl font-bold text-gray-900">Audit Log</h1>
                <p className="text-sm text-gray-500 mt-1">
                    Track all system activities and changes • {pagination.total} records
                </p>
            </div>

            {/* Filters */}
            <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div className="flex items-center justify-between mb-3">
                    <h3 className="text-sm font-semibold text-gray-700">Filters</h3>
                    {hasActiveFilters && (
                        <button onClick={clearFilters} className="text-xs text-red-500 hover:text-red-700">
                            Clear all
                        </button>
                    )}
                </div>
                <div className="grid grid-cols-2 md:grid-cols-5 gap-3">
                    <select
                        value={filters.event}
                        onChange={(e) => handleFilterChange('event', e.target.value)}
                        className="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">All Events</option>
                        <option value="created">Created</option>
                        <option value="updated">Updated</option>
                        <option value="deleted">Deleted</option>
                        <option value="login">Login</option>
                        <option value="logout">Logout</option>
                        <option value="approved">Approved</option>
                        <option value="submitted">Submitted</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <select
                        value={filters.auditable_type}
                        onChange={(e) => handleFilterChange('auditable_type', e.target.value)}
                        className="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">All Models</option>
                        <option value="App\Models\PurchaseOrder">Purchase Order</option>
                        <option value="App\Models\SupplierQuotation">Supplier Quotation</option>
                        <option value="App\Models\PurchaseRequisition">Purchase Requisition</option>
                        <option value="App\Models\StockPicking">Stock Picking</option>
                        <option value="App\Models\InventoryAdjustment">Inventory Adjustment</option>
                        <option value="App\Models\Product">Product</option>
                        <option value="App\Models\Vendor">Vendor</option>
                        <option value="App\Models\Customer">Customer</option>
                        <option value="App\Models\User">User</option>
                    </select>
                    <input
                        type="date"
                        value={filters.date_from}
                        onChange={(e) => handleFilterChange('date_from', e.target.value)}
                        placeholder="From date"
                        className="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <input
                        type="date"
                        value={filters.date_to}
                        onChange={(e) => handleFilterChange('date_to', e.target.value)}
                        placeholder="To date"
                        className="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <button
                        onClick={() => fetchLogs(1)}
                        className="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        Apply
                    </button>
                </div>
            </div>

            {error && (
                <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                    {error}
                </div>
            )}

            {/* Audit Log Table */}
            <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                {loading ? (
                    <div className="flex items-center justify-center h-48">
                        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600" />
                    </div>
                ) : logs.length === 0 ? (
                    <div className="text-center py-16">
                        <div className="text-6xl mb-4">📋</div>
                        <h3 className="text-lg font-medium text-gray-900">No audit logs found</h3>
                        <p className="text-gray-500 mt-1">
                            {hasActiveFilters ? 'Try adjusting your filters' : 'Activity will appear here as actions are performed'}
                        </p>
                    </div>
                ) : (
                    <>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead className="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th className="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-4 py-3">Timestamp</th>
                                        <th className="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-4 py-3">User</th>
                                        <th className="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-4 py-3">Event</th>
                                        <th className="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-4 py-3">Model</th>
                                        <th className="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-4 py-3">Record ID</th>
                                        <th className="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-4 py-3">IP Address</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100">
                                    {logs.map((log) => (
                                        <tr key={log.id} className="hover:bg-gray-50 transition-colors">
                                            <td className="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                                                {log.created_at
                                                    ? new Date(log.created_at).toLocaleString('id-ID', {
                                                        day: '2-digit', month: 'short', year: 'numeric',
                                                        hour: '2-digit', minute: '2-digit', second: '2-digit',
                                                    })
                                                    : '-'
                                                }
                                            </td>
                                            <td className="px-4 py-3">
                                                <div className="flex items-center gap-2">
                                                    <div className="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center text-xs font-medium text-gray-600">
                                                        {(log.user?.name || '?')[0].toUpperCase()}
                                                    </div>
                                                    <span className="text-sm text-gray-900">{log.user?.name || '-'}</span>
                                                </div>
                                            </td>
                                            <td className="px-4 py-3">
                                                <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${eventColors[log.event] || 'bg-gray-100 text-gray-600'}`}>
                                                    {log.event}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 text-sm text-gray-700 font-mono">
                                                {formatModelName(log.auditable_type)}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-gray-600">
                                                #{log.auditable_id}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-gray-500 font-mono">
                                                {log.ip_address || '-'}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {/* Pagination */}
                        {pagination.last_page > 1 && (
                            <div className="flex items-center justify-between px-4 py-3 border-t border-gray-200 bg-gray-50">
                                <span className="text-sm text-gray-500">
                                    Page {pagination.current_page} of {pagination.last_page}
                                </span>
                                <div className="flex gap-2">
                                    <button
                                        onClick={() => fetchLogs(pagination.current_page - 1)}
                                        disabled={pagination.current_page <= 1}
                                        className="px-3 py-1 text-sm border border-gray-300 rounded-lg hover:bg-white disabled:opacity-40 disabled:cursor-not-allowed"
                                    >
                                        Previous
                                    </button>
                                    <button
                                        onClick={() => fetchLogs(pagination.current_page + 1)}
                                        disabled={pagination.current_page >= pagination.last_page}
                                        className="px-3 py-1 text-sm border border-gray-300 rounded-lg hover:bg-white disabled:opacity-40 disabled:cursor-not-allowed"
                                    >
                                        Next
                                    </button>
                                </div>
                            </div>
                        )}
                    </>
                )}
            </div>
        </div>
    );
}
