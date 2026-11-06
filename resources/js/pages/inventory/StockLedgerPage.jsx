import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { inventoryApi } from '../../api/endpoints';
import { Card } from '../../components/ui';
import { Button } from '../../components/ui/Button';
import { Spinner } from '../../components/ui/Loader';
import { formatCurrency, formatDate } from '../../components/ui/StatusBadge';
import { ArrowLeft, Package, ArrowDownCircle, ArrowUpCircle } from 'lucide-react';

export default function StockLedgerPage() {
    const { productId } = useParams();
    const navigate = useNavigate();
    const [data, setData] = useState(null);
    const [product, setProduct] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [filters, setFilters] = useState({ date_from: '', date_to: '' });

    useEffect(() => {
        if (productId) load();
    }, [productId]);

    const load = async (nextFilters = filters) => {
        setLoading(true);
        setError(null);
        try {
            const params = {};
            if (nextFilters.date_from) params.date_from = nextFilters.date_from;
            if (nextFilters.date_to) params.date_to = nextFilters.date_to;
            const res = await inventoryApi.stockLedger(productId, params);
            setData(res.data.data);
            setProduct(res.data.data.product);
        } catch (err) {
            setError(err.response?.data?.message || err.message);
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="space-y-6">
            <div className="flex items-center gap-3">
                <button onClick={() => navigate(-1)} className="p-2 rounded-lg hover:bg-gray-100">
                    <ArrowLeft className="h-5 w-5" />
                </button>
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Stock Card</h1>
                    <p className="text-sm text-gray-500">
                        {product ? `${product.code} — ${product.name}` : 'Movement history with running balance'}
                    </p>
                </div>
            </div>

            <Card className="p-4">
                <div className="flex flex-wrap items-end gap-3">
                    <div>
                        <label className="block text-xs font-medium text-gray-600 mb-1">From</label>
                        <input type="date" value={filters.date_from}
                            onChange={e => setFilters(p => ({ ...p, date_from: e.target.value }))}
                            className="rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label className="block text-xs font-medium text-gray-600 mb-1">To</label>
                        <input type="date" value={filters.date_to}
                            onChange={e => setFilters(p => ({ ...p, date_to: e.target.value }))}
                            className="rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                    </div>
                    <Button onClick={() => load()} disabled={loading}>
                        {loading ? 'Loading...' : 'Apply'}
                    </Button>
                </div>
            </Card>

            {error && <div className="rounded-lg bg-red-50 p-4 text-sm text-red-700">{error}</div>}

            {loading ? (
                <div className="flex justify-center py-20"><Spinner /></div>
            ) : !data ? null : (
                <>
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <Card className="p-4">
                            <div className="flex items-center gap-2 text-gray-500 text-xs uppercase tracking-wide mb-1">
                                <ArrowDownCircle className="h-4 w-4" /> Total In
                            </div>
                            <p className="text-xl font-bold text-green-700">{data.total_in}</p>
                        </Card>
                        <Card className="p-4">
                            <div className="flex items-center gap-2 text-gray-500 text-xs uppercase tracking-wide mb-1">
                                <ArrowUpCircle className="h-4 w-4" /> Total Out
                            </div>
                            <p className="text-xl font-bold text-red-700">{data.total_out}</p>
                        </Card>
                        <Card className="p-4">
                            <div className="flex items-center gap-2 text-gray-500 text-xs uppercase tracking-wide mb-1">
                                <Package className="h-4 w-4" /> Closing Balance
                            </div>
                            <p className="text-xl font-bold text-gray-900">{data.closing_balance}</p>
                        </Card>
                        <Card className="p-4">
                            <div className="text-gray-500 text-xs uppercase tracking-wide mb-1">Movements</div>
                            <p className="text-xl font-bold text-gray-900">{data.rows.length}</p>
                        </Card>
                    </div>

                    <Card className="overflow-hidden">
                        <div className="overflow-x-auto">
                            <table className="min-w-full text-sm">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-4 py-3 text-left font-medium text-gray-600">Date</th>
                                        <th className="px-4 py-3 text-left font-medium text-gray-600">Reference</th>
                                        <th className="px-4 py-3 text-left font-medium text-gray-600">Type</th>
                                        <th className="px-4 py-3 text-left font-medium text-gray-600">From</th>
                                        <th className="px-4 py-3 text-left font-medium text-gray-600">To</th>
                                        <th className="px-4 py-3 text-right font-medium text-gray-600">In</th>
                                        <th className="px-4 py-3 text-right font-medium text-gray-600">Out</th>
                                        <th className="px-4 py-3 text-right font-medium text-gray-600">Balance</th>
                                        <th className="px-4 py-3 text-right font-medium text-gray-600">Value</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100">
                                    <tr className="bg-gray-50">
                                        <td className="px-4 py-3 text-gray-600" colSpan={7}>Opening Balance</td>
                                        <td className="px-4 py-3 text-right font-medium">{data.opening_balance}</td>
                                        <td className="px-4 py-3"></td>
                                    </tr>
                                    {data.rows.length === 0 && (
                                        <tr>
                                            <td colSpan={9} className="px-4 py-10 text-center text-gray-500">
                                                No movements recorded for this product yet.
                                            </td>
                                        </tr>
                                    )}
                                    {data.rows.map(row => (
                                        <tr key={row.id} className="hover:bg-gray-50">
                                            <td className="px-4 py-3 text-gray-600">{formatDate(row.date)}</td>
                                            <td className="px-4 py-3 font-medium text-gray-900">{row.picking_number || '-'}</td>
                                            <td className="px-4 py-3 text-gray-600 capitalize">{row.picking_type || '-'}</td>
                                            <td className="px-4 py-3 text-gray-600">{row.from || '-'}</td>
                                            <td className="px-4 py-3 text-gray-600">{row.to || '-'}</td>
                                            <td className="px-4 py-3 text-right text-green-700">
                                                {row.change > 0 ? row.change : ''}
                                            </td>
                                            <td className="px-4 py-3 text-right text-red-700">
                                                {row.change < 0 ? Math.abs(row.change) : ''}
                                            </td>
                                            <td className="px-4 py-3 text-right font-medium">{row.running_balance}</td>
                                            <td className="px-4 py-3 text-right text-gray-600">
                                                {row.value ? formatCurrency(row.value) : '-'}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </Card>
                </>
            )}
        </div>
    );
}
