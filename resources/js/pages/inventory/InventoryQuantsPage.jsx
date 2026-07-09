import { useState, useEffect, useCallback } from 'react';
import { Search, Package, AlertTriangle, RefreshCw } from 'lucide-react';
import { inventoryApi } from '../../api/endpoints';
import { Spinner } from '../../components/ui/Loader';

export default function InventoryQuantsPage() {
    const [quants, setQuants] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [search, setSearch] = useState('');
    const [currentPage, setCurrentPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [total, setTotal] = useState(0);
    const [lowStockItems, setLowStockItems] = useState(null);

    const fetchData = useCallback(async (params = {}) => {
        setLoading(true);
        try {
            const response = await inventoryApi.quants({ page: currentPage, search, per_page: 15, ...params });
            const meta = response.data?.data;
            setQuants(meta?.data || []);
            setCurrentPage(meta?.current_page || 1);
            setLastPage(meta?.last_page || 1);
            setTotal(meta?.total || 0);
            setError(null);
        } catch (err) { setError(err); } finally { setLoading(false); }
    }, [currentPage, search]);

    useEffect(() => { fetchData(); }, [fetchData]);

    const handlePageChange = (p) => { setCurrentPage(p); };
    const handleSearchChange = (s) => { setSearch(s); setCurrentPage(1); };
    const [lowStockLoading, setLowStockLoading] = useState(false);

    const loadLowStock = async () => {
        setLowStockLoading(true);
        try {
            const res = await inventoryApi.lowStock();
            setLowStockItems(res.data.data || []);
        } catch {} finally { setLowStockLoading(false); }
    };

    return (
        <div className="space-y-6">
            {/* Header */}
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Inventory Overview</h1>
                    <p className="text-sm text-gray-500">Current stock levels across all locations</p>
                </div>
                <div className="flex gap-2">
                    <button onClick={() => fetchData(1)} className="flex items-center gap-2 rounded-lg border border-gray-300 px-3 py-2 text-sm hover:bg-gray-50">
                        <RefreshCw className="h-4 w-4" /> Refresh
                    </button>
                    <button onClick={loadLowStock} className="flex items-center gap-2 rounded-lg bg-orange-50 border border-orange-200 px-3 py-2 text-sm text-orange-700 hover:bg-orange-100">
                        <AlertTriangle className="h-4 w-4" /> Low Stock Alert
                    </button>
                </div>
            </div>

            {/* Low Stock Alert Banner */}
            {lowStockItems !== null && (
                <div className="bg-orange-50 border border-orange-200 rounded-xl p-4">
                    <div className="flex items-center justify-between mb-3">
                        <h3 className="font-semibold text-orange-800 flex items-center gap-2">
                            <AlertTriangle className="h-5 w-5" /> Low Stock Products
                            <span className="ml-2 bg-orange-200 text-orange-800 text-xs font-medium px-2 py-0.5 rounded-full">{lowStockItems.length}</span>
                        </h3>
                        <button onClick={() => setLowStockItems(null)} className="text-sm text-orange-600 hover:text-orange-800">Dismiss</button>
                    </div>
                    {lowStockItems.length === 0 ? (
                        <p className="text-sm text-orange-600">No products below minimum stock level. ✅</p>
                    ) : (
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            {lowStockItems.map((item, i) => (
                                <div key={i} className="bg-white rounded-lg border border-orange-200 p-3">
                                    <p className="font-medium text-gray-900 text-sm">{item.product?.name || `Product #${item.product_id}`}</p>
                                    <p className="text-xs text-gray-500">Location: {item.stock_location?.name || '-'}</p>
                                    <div className="flex justify-between mt-2 text-sm">
                                        <span className="text-gray-500">On Hand:</span>
                                        <span className="font-bold text-orange-600">{item.on_hand_qty || item.quantity || 0}</span>
                                    </div>
                                    {item.min_qty != null && (
                                        <div className="flex justify-between text-sm">
                                            <span className="text-gray-500">Min Required:</span>
                                            <span className="text-gray-700">{item.min_qty}</span>
                                        </div>
                                    )}
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            )}

            {/* Search */}
            <div className="relative max-w-md">
                <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                <input
                    type="text"
                    value={search}
                    onChange={e => handleSearchChange(e.target.value)}
                    placeholder="Search by product name or code..."
                    className="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                />
            </div>

            {/* Table */}
            {loading ? (
                <div className="flex justify-center py-20"><Spinner /></div>
            ) : (
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Product</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Code</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Location</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Warehouse</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">On Hand</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">Reserved</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">Available</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {quants.map((q, i) => {
                                    const onHand = Number(q.on_hand_qty || q.quantity || 0);
                                    const reserved = Number(q.reserved_qty || 0);
                                    const available = onHand - reserved;
                                    return (
                                        <tr key={q.id || i} className="hover:bg-gray-50">
                                            <td className="px-4 py-3">
                                                <div className="flex items-center gap-2">
                                                    <Package className="h-4 w-4 text-gray-400" />
                                                    <span className="font-medium">{q.product?.name || `Product #${q.product_id}`}</span>
                                                </div>
                                            </td>
                                            <td className="px-4 py-3 text-gray-500">{q.product?.code || '-'}</td>
                                            <td className="px-4 py-3">{q.stock_location?.name || q.location?.name || '-'}</td>
                                            <td className="px-4 py-3">{q.warehouse?.name || '-'}</td>
                                            <td className="px-4 py-3 text-right font-medium">{onHand}</td>
                                            <td className="px-4 py-3 text-right text-gray-500">{reserved}</td>
                                            <td className="px-4 py-3 text-right">
                                                <span className={`font-medium ${available <= 0 ? 'text-red-600' : 'text-green-600'}`}>
                                                    {available}
                                                </span>
                                            </td>
                                        </tr>
                                    );
                                })}
                                {quants.length === 0 && (
                                    <tr><td colSpan={7} className="px-4 py-12 text-center text-gray-400">
                                        <Package className="h-8 w-8 mx-auto mb-2 text-gray-300" />
                                        No stock data found.
                                    </td></tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                    {lastPage > 1 && (
                        <div className="flex items-center justify-between border-t border-gray-200 px-4 py-3">
                            <p className="text-sm text-gray-500">Page {currentPage} of {lastPage} ({total} total)</p>
                            <div className="flex gap-1">
                                {Array.from({ length: Math.min(lastPage, 7) }, (_, i) => i + 1).map(page => (
                                    <button key={page} onClick={() => handlePageChange(page)} className={`px-3 py-1 rounded-lg text-sm ${page === currentPage ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100'}`}>{page}</button>
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
