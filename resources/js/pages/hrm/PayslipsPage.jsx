import { useState } from 'react';
import { Search, Eye, CheckCircle, DollarSign, Trash2 } from 'lucide-react';
import { payslipsApi } from '../../api/endpoints';
import { useCrudApi } from '../../hooks/useCrudApi';
import { StatusBadge, formatDate, formatCurrency } from '../../components/ui/StatusBadge';
import { Button } from '../../components/ui/Button';
import { Spinner } from '../../components/ui/Loader';

export default function PayslipsPage() {
    const {
        data: payslips, loading, saving, error, setError,
        currentPage, lastPage, total,
        search, filterValues,
        fetchData, handlePageChange, handleSearchChange, handleFilterChange,
        destroy, performAction,
    } = useCrudApi(payslipsApi);

    const [view, setView] = useState('list');
    const [selectedId, setSelectedId] = useState(null);
    const [detail, setDetail] = useState(null);
    const [detailLoading, setDetailLoading] = useState(false);

    const loadDetail = async (id) => {
        setDetailLoading(true);
        try {
            const res = await payslipsApi.show(id);
            setDetail(res.data.data);
            setSelectedId(id);
            setView('detail');
        } catch {} finally { setDetailLoading(false); }
    };

    const handleAction = async (action, id) => {
        if (!window.confirm(`${action} this payslip?`)) return;
        const result = await performAction(action === 'confirm' ? payslipsApi.confirm : payslipsApi.pay, id);
        if (result && view === 'detail') loadDetail(id);
    };

    if (view === 'detail' && detail) {
        return (
            <div className="mx-auto max-w-4xl space-y-6">
                <div className="flex items-center gap-3">
                    <button onClick={() => { setView('list'); setDetail(null); }} className="text-gray-500 hover:text-gray-700">← Back</button>
                    <h1 className="text-2xl font-bold text-gray-900">{detail.payslip_number}</h1>
                    <StatusBadge status={detail.status} />
                </div>
                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div className="lg:col-span-2 space-y-4">
                        <div className="rounded-xl border border-gray-200 bg-white p-5">
                            <h3 className="mb-3 font-semibold text-gray-900">Salary Summary</h3>
                            <dl className="grid grid-cols-2 gap-3 text-sm">
                                <dt className="text-gray-500">Employee</dt><dd className="text-gray-900">{detail.employee?.full_name || '-'}</dd>
                                <dt className="text-gray-500">Payroll</dt><dd className="text-gray-900">{detail.payroll?.payroll_number || '-'}</dd>
                                <dt className="text-gray-500">Basic Salary</dt><dd className="text-gray-900">{formatCurrency(detail.basic_salary)}</dd>
                                <dt className="text-gray-500">Total Allowances</dt><dd className="text-green-600">+{formatCurrency(detail.total_allowances)}</dd>
                                <dt className="text-gray-500">Total Overtime</dt><dd className="text-green-600">+{formatCurrency(detail.total_overtime)}</dd>
                                <dt className="text-gray-500">Total Bonus</dt><dd className="text-green-600">+{formatCurrency(detail.total_bonus)}</dd>
                                <dt className="text-gray-500">Total Deductions</dt><dd className="text-red-600">-{formatCurrency(detail.total_deductions)}</dd>
                                <dt className="text-gray-500">Total Tax</dt><dd className="text-red-600">-{formatCurrency(detail.total_tax)}</dd>
                                <dt className="text-gray-500">Total Insurance</dt><dd className="text-red-600">-{formatCurrency(detail.total_insurance)}</dd>
                                <dt className="text-gray-500 font-semibold">Gross Salary</dt><dd className="font-semibold text-gray-900">{formatCurrency(detail.gross_salary)}</dd>
                                <dt className="text-gray-500 font-semibold">Net Salary</dt><dd className="text-lg font-bold text-indigo-600">{formatCurrency(detail.net_salary)}</dd>
                            </dl>
                        </div>
                        <div className="rounded-xl border border-gray-200 bg-white p-5">
                            <h3 className="mb-3 font-semibold text-gray-900">Attendance</h3>
                            <dl className="grid grid-cols-2 gap-3 text-sm">
                                <dt className="text-gray-500">Work Days</dt><dd className="text-gray-900">{detail.work_days}</dd>
                                <dt className="text-gray-500">Late Minutes</dt><dd className="text-gray-900">{detail.late_minutes}</dd>
                                <dt className="text-gray-500">Overtime Hours</dt><dd className="text-gray-900">{detail.overtime_hours}</dd>
                                <dt className="text-gray-500">Unpaid Leave Days</dt><dd className="text-gray-900">{detail.unpaid_leave_days}</dd>
                            </dl>
                        </div>
                        {detail.lines?.length > 0 && (
                            <div className="rounded-xl border border-gray-200 bg-white p-5">
                                <h3 className="mb-3 font-semibold text-gray-900">Component Details</h3>
                                <table className="w-full text-sm">
                                    <thead><tr className="border-b"><th className="py-2 text-left">Component</th><th className="py-2 text-left">Type</th><th className="py-2 text-right">Amount</th></tr></thead>
                                    <tbody className="divide-y">
                                        {detail.lines.sort((a,b) => a.sequence - b.sequence).map(line => (
                                            <tr key={line.id}>
                                                <td className="py-2">{line.component_name || line.payroll_component?.name}</td>
                                                <td className="py-2 capitalize">{line.component_type}</td>
                                                <td className="py-2 text-right font-medium">{formatCurrency(line.amount)}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </div>
                    <div className="space-y-4">
                        <div className="rounded-xl border border-gray-200 bg-white p-5">
                            <h3 className="mb-3 font-semibold text-gray-900">Actions</h3>
                            <div className="space-y-2">
                                {detail.status === 'draft' && <Button onClick={() => handleAction('confirm', detail.id)} className="w-full flex items-center justify-center gap-2"><CheckCircle className="h-4 w-4" /> Confirm</Button>}
                                {(detail.status === 'verified' || detail.status === 'approved') && <Button onClick={() => handleAction('pay', detail.id)} className="w-full flex items-center justify-center gap-2"><DollarSign className="h-4 w-4" /> Mark Paid</Button>}
                                {!['paid'].includes(detail.status) && <Button variant="danger" onClick={() => { if (window.confirm('Delete?')) { destroy(detail.id); setView('list'); } }} className="w-full flex items-center justify-center gap-2"><Trash2 className="h-4 w-4" /> Delete</Button>}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="space-y-6">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Payslips</h1>
                    <p className="text-sm text-gray-500">{total} payslips total</p>
                </div>
            </div>
            <div className="flex gap-3">
                <div className="relative flex-1">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <input type="text" value={search} onChange={e => handleSearchChange(e.target.value)}
                        placeholder="Search payslips..." className="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" />
                </div>
                <select value={filterValues.status || ''} onChange={e => handleFilterChange({ ...filterValues, status: e.target.value })}
                    className="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="verified">Verified</option>
                    <option value="approved">Approved</option>
                    <option value="paid">Paid</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            {loading ? <div className="flex justify-center py-20"><Spinner /></div> : (
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Payslip #</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Employee</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">Gross</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">Deductions</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">Net</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                                    <th className="px-4 py-3 text-center font-medium text-gray-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {payslips.map(ps => (
                                    <tr key={ps.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3">
                                            <button onClick={() => loadDetail(ps.id)} className="font-medium text-indigo-600 hover:text-indigo-800">{ps.payslip_number}</button>
                                        </td>
                                        <td className="px-4 py-3 text-gray-900">{ps.employee?.full_name || '-'}</td>
                                        <td className="px-4 py-3 text-right text-gray-600">{formatCurrency(ps.gross_salary)}</td>
                                        <td className="px-4 py-3 text-right text-red-600">{formatCurrency(ps.total_deductions)}</td>
                                        <td className="px-4 py-3 text-right font-medium text-gray-900">{formatCurrency(ps.net_salary)}</td>
                                        <td className="px-4 py-3"><StatusBadge status={ps.status} /></td>
                                        <td className="px-4 py-3">
                                            <div className="flex items-center justify-center gap-1">
                                                <button onClick={() => loadDetail(ps.id)} className="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100" title="View"><Eye className="h-4 w-4" /></button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                                {payslips.length === 0 && <tr><td colSpan={7} className="px-4 py-12 text-center text-gray-400">No payslips found.</td></tr>}
                            </tbody>
                        </table>
                    </div>
                    {lastPage > 1 && (
                        <div className="flex items-center justify-between border-t border-gray-200 px-4 py-3">
                            <p className="text-sm text-gray-500">Page {currentPage} of {lastPage} ({total} total)</p>
                            <div className="flex gap-1">
                                {Array.from({ length: Math.min(lastPage, 7) }, (_, i) => i + 1).map(page => (
                                    <button key={page} onClick={() => handlePageChange(page)}
                                        className={`px-3 py-1 rounded-lg text-sm ${page === currentPage ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100'}`}>{page}</button>
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
