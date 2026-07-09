import { useEffect, useState } from 'react';
import { Card, StatCard } from '../../components/ui';
import { DollarSign, Receipt, Landmark, Wallet, TrendingUp, TrendingDown } from 'lucide-react';
import api from '../../api/client';

export default function FinanceDashboardPage() {
    const [summary, setSummary] = useState({});
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        api.get('/v1/finance/dashboard').then((res) => {
            setSummary(res.data.data || {});
        }).finally(() => setLoading(false));
    }, []);

    if (loading) return <div className="text-sm text-gray-500">Loading finance dashboard...</div>;

    return (
        <div className="space-y-6">
            <div>
                <h1 className="text-2xl font-bold text-gray-900">Finance Dashboard</h1>
                <p className="text-sm text-gray-500">Overview of receivables, payables, and cash positions.</p>
            </div>
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <StatCard label="Total Receivable" value={summary.receivable ?? 0} icon={Receipt} color="green" />
                <StatCard label="Total Payable" value={summary.payable ?? 0} icon={Receipt} color="yellow" />
                <StatCard label="Cash Balance" value={summary.cash_balance ?? 0} icon={Wallet} color="blue" />
                <StatCard label="Bank Balance" value={summary.bank_balance ?? 0} icon={Landmark} color="indigo" />
                <StatCard label="Revenue This Month" value={summary.revenue_this_month ?? 0} icon={TrendingUp} color="green" />
                <StatCard label="Expense This Month" value={summary.expense_this_month ?? 0} icon={TrendingDown} color="red" />
            </div>
            <Card title="Finance Quick Access" subtitle="Core accounting workflows">
                <div className="grid gap-3 md:grid-cols-3">
                    <div className="rounded-lg border bg-gray-50 p-4">
                        <p className="text-sm font-semibold text-gray-900">Journal Entries</p>
                        <p className="text-sm text-gray-500">Create balanced manual journal entries and post them safely.</p>
                    </div>
                    <div className="rounded-lg border bg-gray-50 p-4">
                        <p className="text-sm font-semibold text-gray-900">Invoices</p>
                        <p className="text-sm text-gray-500">Manage customer invoices and vendor bills with status workflow.</p>
                    </div>
                    <div className="rounded-lg border bg-gray-50 p-4">
                        <p className="text-sm font-semibold text-gray-900">Payments</p>
                        <p className="text-sm text-gray-500">Register customer and vendor payments with allocation support.</p>
                    </div>
                </div>
            </Card>
        </div>
    );
}
