import { useState } from 'react';
import { Plus, Search, Eye, Edit2, Trash2, Send, CheckCircle, XCircle, DollarSign } from 'lucide-react';
import { expenseClaimsApi } from '../../api/endpoints';
import { useCrudApi } from '../../hooks/useCrudApi';
import { StatusBadge, formatDate, formatCurrency } from '../../components/ui/StatusBadge';
import { Button } from '../../components/ui/Button';
import { Spinner } from '../../components/ui/Loader';

export default function ExpenseClaimsPage() {
    const {
        data: claims, loading, saving, error, setError,
        currentPage, lastPage, total,
        search, filterValues,
        fetchData, handlePageChange, handleSearchChange, handleFilterChange,
        store, update, destroy, performAction,
    } = useCrudApi(expenseClaimsApi);

    const [view, setView] = useState('list');
    const [selectedId, setSelectedId] = useState(null);
    const [detail, setDetail] = useState(null);
    const [detailLoading, setDetailLoading] = useState(false);
    const [form, setForm] = useState({});
    const [formErrors, setFormErrors] = useState({});

    const loadDetail = async (id) => {
        setDetailLoading(true);
        try {
            const res = await expenseClaimsApi.show(id);
            setDetail(res.data.data);
            setSelectedId(id);
            setView('detail');
        } catch {} finally { setDetailLoading(false); }
    };

    const openForm = (claim = null) => {
        if (claim) {
            setForm({
                employee_id: claim.employee_id || '', expense_date: claim.expense_date || '',
                description: claim.description || '', company_id: claim.company_id || '',
                branch_id: claim.branch_id || '',
            });
            setSelectedId(claim.id);
            setView('edit');
        } else {
            setForm({ employee_id: '', expense_date: new Date().toISOString().slice(0, 10), description: '', company_id: '', branch_id: '' });
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
        if (!window.confirm(`${action} this expense claim?`)) return;
        const actionMap = {
            submit: expenseClaimsApi.submit, approve: expenseClaimsApi.approve,
            reject: expenseClaimsApi.reject, markPaid: expenseClaimsApi.markPaid,
        };
        const result = await performAction(actionMap[action], id);
        if (result && view === 'detail') loadDetail(id);
    };

    if (view === 'create' || view === 'edit') {
        const f = form; const set = (k, v) => setForm({ ...form, [k]: v });
        return (
            <div className="mx-auto max-w-2xl space-y-6">
                <div className="flex items-center gap-3">
                    <button onClick={() => { setView(selectedId ? 'detail' : 'list'); setError(null); }} className="text-gray-500 hover:text-gray-700">← Back</button>
                    <h1 className="text-2xl font-bold text-gray-900">{view === 'edit' ? 'Edit' : 'New'} Expense Claim</h1>
                </div>
                <form onSubmit={handleSubmit} className="space-y-4 rounded-xl border border-gray-200 bg-white p-6">
                    <div className="grid grid-cols-2 gap-4">
                        <Field label="Employee ID *" error={formErrors.employee_id}>
                            <input value={f.employee_id} onChange={e => set('employee_id', e.target.value)} className="input-field" required />
                        </Field>
                        <Field label="Expense Date *" error={formErrors.expense_date}>
                            <input type="date" value={f.expense_date} onChange={e => set('expense_date', e.target.value)} className="input-field" required />
                        </Field>
                    </div>
                    <Field label="Description" error={formErrors.description}>
                        <textarea value={f.description} onChange={e => set('description', e.target.value)} className="input-field" rows={3} />
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
                    <h1 className="text-2xl font-bold text-gray-900">{detail.expense_number}</h1>
                    <StatusBadge status={detail.status} />
                </div>
                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div className="lg:col-span-2 space-y-4">
                        <div className="rounded-xl border border-gray-200 bg-white p-5">
                            <h3 className="mb-3 font-semibold text-gray-900">Details</h3>
                            <dl className="grid grid-cols-2 gap-3 text-sm">
                                <dt className="text-gray-500">Employee</dt><dd className="text-gray-900">{detail.employee?.full_name || '-'}</dd>
                                <dt className="text-gray-500">Date</dt><dd className="text-gray-900">{formatDate(detail.expense_date)}</dd>
                                <dt className="text-gray-500">Total Amount</dt><dd className="font-semibold text-gray-900">{formatCurrency(detail.total_amount)}</dd>
                                <dt className="text-gray-500">Description</dt><dd className="text-gray-900">{detail.description || '-'}</dd>
                            </dl>
                        </div>
                        {detail.lines?.length > 0 && (
                            <div className="rounded-xl border border-gray-200 bg-white p-5">
                                <h3 className="mb-3 font-semibold text-gray-900">Expense Lines</h3>
                                <table className="w-full text-sm">
                                    <thead><tr className="border-b"><th className="py-2 text-left">Category</th><th className="py-2 text-left">Description</th><th className="py-2 text-left">Date</th><th className="py-2 text-right">Amount</th></tr></thead>
                                    <tbody className="divide-y">
                                        {detail.lines.map(line => (
                                            <tr key={line.id}>
                                                <td className="py-2">{line.category}</td>
                                                <td className="py-2">{line.description || '-'}</td>
                                                <td className="py-2">{formatDate(line.expense_date)}</td>
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
                                {detail.status === 'draft' && <>
                                    <Button onClick={() => openForm(detail)} className="w-full flex items-center justify-center gap-2"><Edit2 className="h-4 w-4" /> Edit</Button>
                                    <Button onClick={() => handleAction('submit', detail.id)} className="w-full flex items-center justify-center gap-2"><Send className="h-4 w-4" /> Submit</Button>
                                </>}
                                {detail.status === 'submitted' && <>
                                    <Button onClick={() => handleAction('approve', detail.id)} className="w-full flex items-center justify-center gap-2"><CheckCircle className="h-4 w-4" /> Approve</Button>
                                    <Button variant="danger" onClick={() => handleAction('reject', detail.id)} className="w-full flex items-center justify-center gap-2"><XCircle className="h-4 w-4" /> Reject</Button>
                                </>}
                                {detail.status === 'approved' && <Button onClick={() => handleAction('markPaid', detail.id)} className="w-full flex items-center justify-center gap-2"><DollarSign className="h-4 w-4" /> Mark Paid</Button>}
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
                    <h1 className="text-2xl font-bold text-gray-900">Expense Claims</h1>
                    <p className="text-sm text-gray-500">{total} claims total</p>
                </div>
                <Button onClick={() => openForm()} className="flex items-center gap-2">
                    <Plus className="h-4 w-4" /> New Claim
                </Button>
            </div>
            <div className="flex gap-3">
                <div className="relative flex-1">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <input type="text" value={search} onChange={e => handleSearchChange(e.target.value)}
                        placeholder="Search claims..." className="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" />
                </div>
                <select value={filterValues.status || ''} onChange={e => handleFilterChange({ ...filterValues, status: e.target.value })}
                    className="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="submitted">Submitted</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="paid">Paid</option>
                </select>
            </div>
            {loading ? <div className="flex justify-center py-20"><Spinner /></div> : (
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Claim #</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Employee</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Date</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">Amount</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                                    <th className="px-4 py-3 text-center font-medium text-gray-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {claims.map(c => (
                                    <tr key={c.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3">
                                            <button onClick={() => loadDetail(c.id)} className="font-medium text-indigo-600 hover:text-indigo-800">{c.expense_number}</button>
                                        </td>
                                        <td className="px-4 py-3 text-gray-900">{c.employee?.full_name || '-'}</td>
                                        <td className="px-4 py-3 text-gray-600">{formatDate(c.expense_date)}</td>
                                        <td className="px-4 py-3 text-right font-medium text-gray-900">{formatCurrency(c.total_amount)}</td>
                                        <td className="px-4 py-3"><StatusBadge status={c.status} /></td>
                                        <td className="px-4 py-3">
                                            <div className="flex items-center justify-center gap-1">
                                                <button onClick={() => loadDetail(c.id)} className="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100" title="View"><Eye className="h-4 w-4" /></button>
                                                {c.status === 'draft' && (
                                                    <>
                                                        <button onClick={() => openForm(c)} className="p-1.5 rounded-lg text-blue-500 hover:bg-blue-50" title="Edit"><Edit2 className="h-4 w-4" /></button>
                                                        <button onClick={() => { if (window.confirm('Delete?')) destroy(c.id); }} className="p-1.5 rounded-lg text-red-500 hover:bg-red-50" title="Delete"><Trash2 className="h-4 w-4" /></button>
                                                    </>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                                {claims.length === 0 && <tr><td colSpan={6} className="px-4 py-12 text-center text-gray-400">No expense claims found.</td></tr>}
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
