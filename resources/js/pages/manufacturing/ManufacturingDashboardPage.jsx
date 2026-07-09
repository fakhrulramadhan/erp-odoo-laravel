import { useEffect, useState } from 'react';
import { Card, StatCard } from '../../components/ui';
import {
    Factory, ClipboardCheck, AlertTriangle, Wrench, Package,
    TrendingUp, CheckCircle, XCircle, BarChart3
} from 'lucide-react';
import { manufacturingDashboardApi } from '../../api/endpoints';
import { formatCurrency } from '../../components/ui/StatusBadge';

export default function ManufacturingDashboardPage() {
    const [summary, setSummary] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        manufacturingDashboardApi.summary()
            .then((res) => setSummary(res.data.data || {}))
            .catch(() => {})
            .finally(() => setLoading(false));
    }, []);

    if (loading) return <div className="text-sm text-gray-500">Loading manufacturing dashboard...</div>;

    const mo = summary?.manufacturing_orders || {};
    const qc = summary?.quality_checks || {};
    const scrap = summary?.scrap_orders || {};
    const maint = summary?.maintenance_orders || {};
    const assets = summary?.assets || {};

    return (
        <div className="space-y-6">
            <div>
                <h1 className="text-2xl font-bold text-gray-900">Manufacturing Dashboard</h1>
                <p className="text-sm text-gray-500">Overview of production, quality, maintenance, and assets.</p>
            </div>

            {/* ── MO Stats ──────────────────────────── */}
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <StatCard label="Total MO" value={mo.total ?? 0} icon={Factory} color="indigo" />
                <StatCard label="In Production" value={mo.by_status?.in_production ?? 0} icon={TrendingUp} color="blue" />
                <StatCard label="Finished" value={mo.by_status?.finished ?? 0} icon={CheckCircle} color="green" />
                <StatCard label="Cancelled" value={mo.by_status?.cancelled ?? 0} icon={XCircle} color="red" />
            </div>

            {/* ── Quality / Scrap / Maintenance ─────── */}
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <StatCard label="QC Pass Rate" value={`${qc.pass_rate ?? 0}%`} icon={ClipboardCheck} color="green" />
                <StatCard label="QC Total Checks" value={qc.total ?? 0} icon={ClipboardCheck} color="purple" />
                <StatCard label="Scrap Cost" value={formatCurrency(scrap.total_cost ?? 0)} icon={AlertTriangle} color="red" />
                <StatCard label="Pending Maintenance" value={maint.pending ?? 0} icon={Wrench} color="yellow" />
            </div>

            {/* ── Assets ────────────────────────────── */}
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <StatCard label="Total Assets" value={assets.total ?? 0} icon={Package} color="indigo" />
                <StatCard label="Active Assets" value={assets.active ?? 0} icon={CheckCircle} color="green" />
                <StatCard label="Asset Value" value={formatCurrency(assets.total_value ?? 0)} icon={BarChart3} color="blue" />
                <StatCard label="Total Depreciation" value={formatCurrency(assets.total_depreciation ?? 0)} icon={TrendingUp} color="yellow" />
            </div>

            {/* ── Production Costs by Type ──────────── */}
            {summary?.production_costs_by_type && Object.keys(summary.production_costs_by_type).length > 0 && (
                <Card title="Production Costs by Type" subtitle="Breakdown of manufacturing costs">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Cost Type</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">Total Amount</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {Object.entries(summary.production_costs_by_type).map(([type, amount]) => (
                                    <tr key={type} className="hover:bg-gray-50">
                                        <td className="px-4 py-3 capitalize">{type.replace(/_/g, ' ')}</td>
                                        <td className="px-4 py-3 text-right font-medium">{formatCurrency(amount)}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </Card>
            )}

            {/* ── Work Center Utilization ───────────── */}
            {summary?.work_center_utilization && summary.work_center_utilization.length > 0 && (
                <Card title="Work Center Utilization" subtitle="Efficiency & capacity usage">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Work Center</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">Capacity/Hr</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">Efficiency</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">Cost/Hr</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {summary.work_center_utilization.map((wc) => (
                                    <tr key={wc.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3 font-medium">{wc.name}</td>
                                        <td className="px-4 py-3 text-right">{wc.capacity_per_hour}</td>
                                        <td className="px-4 py-3 text-right">{wc.efficiency}%</td>
                                        <td className="px-4 py-3 text-right">{formatCurrency(wc.cost_per_hour)}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </Card>
            )}

            {/* ── Quick Access ──────────────────────── */}
            <Card title="Manufacturing Quick Access" subtitle="Core production workflows">
                <div className="grid gap-3 md:grid-cols-3">
                    <div className="rounded-lg border bg-gray-50 p-4">
                        <p className="text-sm font-semibold text-gray-900">Manufacturing Orders</p>
                        <p className="text-sm text-gray-500">Create and manage production orders with full workflow from draft to close.</p>
                    </div>
                    <div className="rounded-lg border bg-gray-50 p-4">
                        <p className="text-sm font-semibold text-gray-900">Bill of Materials</p>
                        <p className="text-sm text-gray-500">Define product structures with components, quantities, and routing references.</p>
                    </div>
                    <div className="rounded-lg border bg-gray-50 p-4">
                        <p className="text-sm font-semibold text-gray-900">Quality Checks</p>
                        <p className="text-sm text-gray-500">Run inspections on production output with pass/fail workflow.</p>
                    </div>
                </div>
            </Card>
        </div>
    );
}
