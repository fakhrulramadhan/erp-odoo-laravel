import { useState, useEffect } from 'react';
import { reportsApi } from '../../api/endpoints';
import { Card } from '../../components/ui';
import { Spinner } from '../../components/ui/Loader';
import { formatCurrency } from '../../components/ui/StatusBadge';
import { AlertTriangle, CheckCircle, Package, DollarSign } from 'lucide-react';

export default function InventoryGlReconciliationPage() {
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        reportsApi.inventory({ type: 'gl_reconciliation' })
            .then(res => setData(res.data.data))
            .catch(err => setError(err.response?.data?.message || err.message))
            .finally(() => setLoading(false));
    }, []);

    if (loading) return <div className="flex justify-center py-20"><Spinner /></div>;
    if (error) return <div className="rounded-lg bg-red-50 p-4 text-sm text-red-700">{error}</div>;
    if (!data) return null;

    const reconciled = data.is_reconciled;

    return (
        <div className="space-y-6">
            <div>
                <h1 className="text-2xl font-bold text-gray-900">Inventory vs General Ledger</h1>
                <p className="text-sm text-gray-500 mt-1">
                    Compares the value of stock in the warehouse against the Inventory account balance.
                </p>
            </div>

            <div className={`rounded-xl border p-4 flex items-start gap-3 ${reconciled ? 'bg-green-50 border-green-200' : 'bg-amber-50 border-amber-200'}`}>
                {reconciled
                    ? <CheckCircle className="h-5 w-5 text-green-600 mt-0.5" />
                    : <AlertTriangle className="h-5 w-5 text-amber-600 mt-0.5" />}
                <div>
                    <p className={`text-sm font-medium ${reconciled ? 'text-green-800' : 'text-amber-800'}`}>
                        {reconciled ? 'Reconciled' : 'Variance detected'}
                    </p>
                    <p className={`text-sm ${reconciled ? 'text-green-700' : 'text-amber-700'}`}>
                        {reconciled
                            ? 'Stock value matches the Inventory account.'
                            : `Stock valuation and the Inventory account differ by ${formatCurrency(Math.abs(data.variance))}. A journal may have been skipped or posted twice.`}
                    </p>
                </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Card className="p-4">
                    <div className="flex items-center gap-2 text-gray-500 text-xs uppercase tracking-wide mb-1">
                        <Package className="h-4 w-4" /> Stock Value
                    </div>
                    <p className="text-xl font-bold text-gray-900">{formatCurrency(data.stock_value)}</p>
                </Card>
                <Card className="p-4">
                    <div className="flex items-center gap-2 text-gray-500 text-xs uppercase tracking-wide mb-1">
                        <DollarSign className="h-4 w-4" /> GL {data.inventory_account.code}
                    </div>
                    <p className="text-xl font-bold text-gray-900">{formatCurrency(data.inventory_account.balance)}</p>
                    <p className="text-xs text-gray-500 mt-1">{data.inventory_account.name}</p>
                </Card>
                <Card className="p-4">
                    <div className="flex items-center gap-2 text-gray-500 text-xs uppercase tracking-wide mb-1">
                        <AlertTriangle className="h-4 w-4" /> Variance
                    </div>
                    <p className={`text-xl font-bold ${reconciled ? 'text-gray-900' : 'text-amber-600'}`}>
                        {formatCurrency(data.variance)}
                    </p>
                </Card>
            </div>

            <Card className="overflow-hidden">
                <div className="px-6 py-4 border-b border-gray-200">
                    <h3 className="font-semibold text-gray-900">Stock Value by Warehouse</h3>
                </div>
                <table className="min-w-full text-sm">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-6 py-3 text-left font-medium text-gray-600">Warehouse</th>
                            <th className="px-6 py-3 text-right font-medium text-gray-600">Stock Value</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {data.by_warehouse.length === 0 && (
                            <tr><td colSpan={2} className="px-6 py-8 text-center text-gray-500">No stock recorded.</td></tr>
                        )}
                        {data.by_warehouse.map(w => (
                            <tr key={w.warehouse_id} className="hover:bg-gray-50">
                                <td className="px-6 py-3 text-gray-900">{w.warehouse_name}</td>
                                <td className="px-6 py-3 text-right font-medium">{formatCurrency(w.stock_value)}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </Card>

            <Card className="p-4">
                <p className="text-xs text-gray-500">
                    Work In Progress ({data.wip_account.code}) balance: <span className="font-medium text-gray-700">{formatCurrency(data.wip_account.balance)}</span>
                    {' '}— this account is cleared when production completes.
                </p>
            </Card>
        </div>
    );
}
