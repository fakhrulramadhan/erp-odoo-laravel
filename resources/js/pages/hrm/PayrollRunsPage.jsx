import { useState } from 'react';
import { Plus, Search, Eye, Edit2, Trash2, CheckCircle, XCircle, Play, Flag, Lock } from 'lucide-react';
import { payrollRunsApi, payslipsApi } from '../../api/endpoints';
import { useCrudApi } from '../../hooks/useCrudApi';
import { StatusBadge, formatDate, formatCurrency } from '../../components/ui/StatusBadge';
import { Button } from '../../components/ui/Button';
import { Spinner } from '../../components/ui/Loader';

export default function PayrollRunsPage() {
    const {
        data: runs, loading, saving, error, setError,
        currentPage, lastPage, total,
        search, filterValues,
        fetchData, handlePageChange, handleSearchChange, handleFilterChange,
        store, update, destroy, performAction,
    } = useCrudApi(payrollRunsApi);

    const [view, setView] = useState('list');
    const [selectedId, setSelectedId] = useState(null);
    const [detail, setDetail] = useState(null);
    const [detailLoading, setDetailLoading] = useState(false);
    const [form, setForm] = useState({});
    const [formErrors, setFormErrors] = useState({});

    const loadDetail = async (id) => {
        setDetailLoading(true);
        try {
            const res = await payrollRunsApi.show(id);
            setDetail(res.data.data);
            setSelectedId(id);
            setView('detail');
        } catch {} finally { setDetailLoading(false); }
    };

    const openForm = (run = null) => {
        if (run) {
            setForm({
                payroll_period_id: run.payroll_period_id || '', company_id: run.company_id || '',
                branch_id: run.branch_id || '', pay_date: run.pay_date || '', notes: run.notes || '',
            });
            setSelectedId(run.id);
            setView('edit');
        } else {
            setForm({ payroll_period_id: '', company_id: '', branch_id: '', pay_date: '', notes: '' });
            setSelectedId(null);
            setView('create');
        }
        setFormErrors({});
        setError(null);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setFormErrors({});
        let ok;
        if (view === 'edit') { ok = await update(selectedId, form); if (ok) loadDetail(selectedId); }
        else { ok = await store(form); if (ok) setView('list'); }
        if (!ok && error?.errors) setFormErrors(error.errors);
    };

    const handleAction = async (action, id) => {
        const actionMap = {
            process: payrollRunsApi.process, confirm: payrollRunsApi.confirm,
            approve: payrollRunsApi.approve, cancel: payrollRunsApi.cancel,
        };
        if (!window.confirm(`${action} this payroll?`)) return;
        const result = await performAction(actionMap[action], id);
        if (result && view === 'detail') loadDetail(id);
    };

    const statusFilter = filterValues.status || '';

    if (view === 'create' || view === 'edit') {
        const f = form; const set = (k, v) => setForm({ ...form, [k]: v });
        return (
            <div className="mx-auto max-w-2xl space-y-6">
                <div className="flex items-center gap-3">
                    <button onClick={() => { setView(selectedId ? 'detail' : 'list'); setError(null); }} className="text-gray-500 hover:text-gray-700">← Back</button>
                    <h1 className="text-2xl font-bold text-gray-900">{view === 'edit' ? 'Edit' : 'New'} Payroll Run</h1>
                </div>
                <form onSubmit={handleSubmit} className="space-y-4 rounded-xl border border-gray-200 bg-white p-6">
                    <div className="grid grid-cols-2 gap-4">
                        <Field label="Payroll Period ID *" error={formErrors.payroll_period_id}>
                            <input value={f.payroll_period_id} onChange={e => set('payroll_period_id', e.target.value)} className="input-field" required />
                        </Field>
                        <Field label="Pay Date *" error={formErrors.pay_date}>
                            <input type="date" value={f.pay_date} onChange={e => set('pay_date', e.target.value)} className="input-field" required />
                        </Field>
                    </div>
                    <Field label="Notes" error={formErrors.notes}>
                        <textarea value={f.notes} onChange={e => set('notes', e.target.value)} className="input-field" rows={2} />
                    </Field>
                    <div className="flex justify-end gap-3">
                        <Button type="button" variant="secondary" onClick={() => { setView(selectedId ? 'detail' : 'list'); setError(null); }}>Cancel</Button>
                        <Button type="submit" disabled={saving}>{saving ? 'Saving...' : 'Save'}</Button>
                    </div>
                </form>
            </div>
        );
    }

    if (view === 'detail' && detail) {
        return (
            <div className="mx-auto max-w-4xl space-y-6">
                <div className="flex items-center gap-3">
                    <button onClick={() => { setView('list'); setDetail(null); }} className="text-gray-500 hover:text-gray-700">← Back</button>
                    <h1 className="text-2xl font-bold text-gray-900">{detail.payroll_number}</h1>
                    <StatusBadge status={detail.status} />
                </div>
                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div className="lg:col-span-2 space-y-4">
                        <div className="rounded-xl border border-gray-200 bg-white p-5">
                            <h3 className="mb-3 font-semibold text-gray-900">Payroll Details</h3>
                            <dl className="grid grid-cols-2 gap-3 text-sm">
                                <dt className="text-gray-500">Period</dt><dd className="text-gray-900">{detail.payroll_period?.name || '-'}</dd>
                                <dt className="text-gray-500">Pay Date</dt><dd className="text-gray-900">{formatDate(detail.pay_date)}</dd>
                                <dt className="text-gray-500">Total Employees</dt><dd className="text-gray-900">{detail.total_employees}</dd>
                                <dt className="text-gray-500">Total Gross</dt><dd className="text-gray-900">{formatCurrency(detail.total_gross)}</dd>
                                <dt className="text-gray-500">Total Deductions</dt><dd className="text-gray-900">{formatCurrency(detail.total_deductions)}</dd>
                                <dt className="text-gray-500">Total Net</dt><dd className="font-semibold text-gray-900">{formatCurrency(detail.total_net)}</dd>
                            </dl>
                        </div>
                        {detail.payslips?.length > 0 && (
                            <div className="rounded-xl border border-gray-200 bg-white p-5">
                                <h3 className="mb-3 font-semibold text-gray-900">Payslips ({detail.payslips.length})</h3>
                                <table className="w-full text-sm">
                                    <thead><tr className="border-b"><th className="py-2 text-left">Employee</th><th className="py-2 text-right">Gross</th><th className="py-2 text-right">Deductions</th><th className="py-2 text-right">Net</th><th className="py-2 text-left">Status</th></tr></thead>
                                    <tbody className="divide-y">
                                        {detail.payslips.map(ps => (
                                            <tr key={ps.id}>
                                                <td className="py-2">{ps.employee?.full_name || '-'}</td>
                                                <td className="py-2 text-right">{formatCurrency(ps.gross_salary)}</td>
                                                <td className="py-2 text-right">{formatCurrency(ps.total_deductions)}</td>
                                                <td className="py-2 text-right font-medium">{formatCurrency(ps.net_salary)}</td>
                                                <td className="py-2"><StatusBadge status={ps.status} /></td>
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
                                {detail.status === 'draft' && <Button onClick={() => handleAction('process', detail.id)} className="w-full flex items-center justify-center gap-2"><Play className="h-4 w-4" /> Process</Button>}
                                {(detail.status === 'computed' || detail.status === 'computing') && <Button onClick={() => handleAction('confirm', detail.id)} className="w-full flex items-center justify-center gap-2"><CheckCircle className="h-4 w-4" /> Confirm</Button>}
                                {detail.status === 'pending_approval' && <Button onClick={() => handleAction('approve', detail.id)} className="w-full flex items-center justify-center gap-2"><Flag className="h-4 w-4" /> Approve</Button>}
                                {!['paid', 'cancelled'].includes(detail.status) && (
                                    <Button variant="secondary" onClick={() => openForm(detail)} className="w-full flex items-center justify-center gap-2"><Edit2 className="h-4 w-4" /> Edit</Button>
                                )}
                                {!['paid'].includes(detail.status) && (
                                    <Button variant="danger" onClick={() => handleAction('cancel', detail.id)} className="w-full flex items-center justify-center gap-2"><XCircle className="h-4 w-4" /> Cancel</Button>
                                )}
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
                    <h1 className="text-2xl font-bold text-gray-900">Payroll Runs</h1>
                    <p className="text-sm text-gray-500">{total} payroll runs total</p>
                </div>
                <Button onClick={() => openForm()} className="flex items-center gap-2">
                    <Plus className="h-4 w-4" /> New Payroll Run
                </Button>
            </div>
            <div className="flex gap-3">
                <div className="relative flex-1">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <input type="text" value={search} onChange={e => handleSearchChange(e.target.value)}
                        placeholder="Search payroll..." className="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" />
                </div>
                <select value={statusFilter} onChange={e => handleFilterChange({ ...filterValues, status: e.target.value })}
                    className="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="computing">Computing</option>
                    <option value="computed">Computed</option>
                    <option value="pending_approval">Pending Approval</option>
                    <option value="approved">Approved</option>
                    <option value="paid">Paid</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            {loading ? <div className="flex justify-center py-20"><Spinner /></div> : (
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Payroll #</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Period</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Pay Date</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">Employees</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">Total Net</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                                    <th className="px-4 py-3 text-center font-medium text-gray-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {runs.map(run => (
                                    <tr key={run.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3">
                                            <button onClick={() => loadDetail(run.id)} className="font-medium text-indigo-600 hover:text-indigo-800">{run.payroll_number}</button>
                                        </td>
                                        <td className="px-4 py-3 text-gray-600">{run.payroll_period?.name || '-'}</td>
                                        <td className="px-4 py-3 text-gray-600">{formatDate(run.pay_date)}</td>
                                        <td className="px-4 py-3 text-right text-gray-600">{run.total_employees}</td>
                                        <td className="px-4 py-3 text-right font-medium text-gray-900">{formatCurrency(run.total_net)}</td>
                                        <td className="px-4 py-3"><StatusBadge status={run.status} /></td>
                                        <td className="px-4 py-3">
                                            <div className="flex items-center justify-center gap-1">
                                                <button onClick={() => loadDetail(run.id)} className="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100" title="View"><Eye className="h-4 w-4" /></button>
                                                {run.status === 'draft' && (
                                                    <>
                                                        <button onClick={() => openForm(run)} className="p-1.5 rounded-lg text-blue-500 hover:bg-blue-50" title="Edit"><Edit2 className="h-4 w-4" /></button>
                                                        <button onClick={() => { if (window.confirm('Delete?')) destroy(run.id); }} className="p-1.5 rounded-lg text-red-500 hover:bg-red-50" title="Delete"><Trash2 className="h-4 w-4" /></button>
                                                    </>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                                {runs.length === 0 && <tr><td colSpan={7} className="px-4 py-12 text-center text-gray-400">No payroll runs found.</td></tr>}
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

function Field({ label, error, children }) {
    return <div><label className="mb-1 block text-sm font-medium text-gray-700">{label}</label>{children}{error && <p className="mt-1 text-xs text-red-600">{Array.isArray(error) ? error[0] : error}</p>}</div>;
}
