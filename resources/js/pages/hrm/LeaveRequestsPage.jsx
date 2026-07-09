import { useState } from 'react';
import { Plus, Search, CheckCircle, XCircle } from 'lucide-react';
import { leaveRequestsApi } from '../../api/endpoints';
import { useCrudApi } from '../../hooks/useCrudApi';
import { StatusBadge, formatDate } from '../../components/ui/StatusBadge';
import { Button } from '../../components/ui/Button';
import { Spinner } from '../../components/ui/Loader';

export default function LeaveRequestsPage() {
    const {
        data: requests, loading, saving, error, setError,
        currentPage, lastPage, total,
        search, filterValues,
        handlePageChange, handleSearchChange, handleFilterChange,
        store, update, destroy, performAction,
    } = useCrudApi(leaveRequestsApi);

    const [view, setView] = useState('list');
    const [selectedId, setSelectedId] = useState(null);
    const [form, setForm] = useState({});
    const [formErrors, setFormErrors] = useState({});

    const openForm = (req = null) => {
        if (req) {
            setForm({
                employee_id: req.employee_id || '', leave_type_id: req.leave_type_id || '',
                start_date: req.start_date || '', end_date: req.end_date || '',
                reason: req.reason || '',
            });
            setSelectedId(req.id);
            setView('edit');
        } else {
            setForm({ employee_id: '', leave_type_id: '', start_date: '', end_date: '', reason: '' });
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
        if (view === 'edit') ok = await update(selectedId, form);
        else ok = await store(form);
        if (ok) setView('list');
        if (!ok && error?.errors) setFormErrors(error.errors);
    };

    const handleApprove = async (id) => {
        if (!window.confirm('Approve this leave request?')) return;
        await performAction(leaveRequestsApi.approve, id);
    };

    const handleReject = async (id) => {
        const reason = window.prompt('Rejection reason:');
        if (reason === null) return;
        await performAction(() => leaveRequestsApi.reject(id, { rejection_reason: reason }), id);
    };

    const handleCancel = async (id) => {
        if (!window.confirm('Cancel this leave request?')) return;
        await performAction(leaveRequestsApi.cancel, id);
    };

    if (view === 'create' || view === 'edit') {
        const f = form; const set = (k, v) => setForm({ ...form, [k]: v });
        return (
            <div className="mx-auto max-w-2xl space-y-6">
                <div className="flex items-center gap-3">
                    <button onClick={() => { setView('list'); setError(null); }} className="text-gray-500 hover:text-gray-700">← Back</button>
                    <h1 className="text-2xl font-bold text-gray-900">{view === 'edit' ? 'Edit' : 'New'} Leave Request</h1>
                </div>
                <form onSubmit={handleSubmit} className="space-y-4 rounded-xl border border-gray-200 bg-white p-6">
                    <div className="grid grid-cols-2 gap-4">
                        <Field label="Employee ID *" error={formErrors.employee_id}>
                            <input value={f.employee_id} onChange={e => set('employee_id', e.target.value)} className="input-field" required />
                        </Field>
                        <Field label="Leave Type ID *" error={formErrors.leave_type_id}>
                            <input value={f.leave_type_id} onChange={e => set('leave_type_id', e.target.value)} className="input-field" required />
                        </Field>
                        <Field label="Start Date *" error={formErrors.start_date}>
                            <input type="date" value={f.start_date} onChange={e => set('start_date', e.target.value)} className="input-field" required />
                        </Field>
                        <Field label="End Date *" error={formErrors.end_date}>
                            <input type="date" value={f.end_date} onChange={e => set('end_date', e.target.value)} className="input-field" required />
                        </Field>
                    </div>
                    <Field label="Reason" error={formErrors.reason}>
                        <textarea value={f.reason} onChange={e => set('reason', e.target.value)} className="input-field" rows={3} />
                    </Field>
                    <div className="flex justify-end gap-3">
                        <Button type="button" variant="secondary" onClick={() => { setView('list'); setError(null); }}>Cancel</Button>
                        <Button type="submit" disabled={saving}>{saving ? 'Saving...' : 'Save'}</Button>
                    </div>
                </form>
            </div>
        );
    }

    return (
        <div className="space-y-6">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Leave Requests</h1>
                    <p className="text-sm text-gray-500">{total} requests total</p>
                </div>
                <Button onClick={() => openForm()} className="flex items-center gap-2">
                    <Plus className="h-4 w-4" /> New Request
                </Button>
            </div>

            <div className="flex gap-3">
                <div className="relative flex-1">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <input type="text" value={search} onChange={e => handleSearchChange(e.target.value)}
                        placeholder="Search requests..." className="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" />
                </div>
                <select value={filterValues.status || ''} onChange={e => handleFilterChange({ ...filterValues, status: e.target.value })}
                    className="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            {loading ? <div className="flex justify-center py-20"><Spinner /></div> : (
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Leave #</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Employee</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Leave Type</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Start</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">End</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">Days</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                                    <th className="px-4 py-3 text-center font-medium text-gray-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {requests.map(req => (
                                    <tr key={req.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3 font-medium text-indigo-600">{req.leave_number}</td>
                                        <td className="px-4 py-3 text-gray-900">{req.employee?.full_name || '-'}</td>
                                        <td className="px-4 py-3 text-gray-600">{req.leave_type?.name || '-'}</td>
                                        <td className="px-4 py-3 text-gray-600">{formatDate(req.start_date)}</td>
                                        <td className="px-4 py-3 text-gray-600">{formatDate(req.end_date)}</td>
                                        <td className="px-4 py-3 text-right text-gray-600">{req.total_days}</td>
                                        <td className="px-4 py-3"><StatusBadge status={req.status} /></td>
                                        <td className="px-4 py-3">
                                            <div className="flex items-center justify-center gap-1">
                                                {req.status === 'pending' && (
                                                    <>
                                                        <button onClick={() => handleApprove(req.id)} className="p-1.5 rounded-lg text-green-500 hover:bg-green-50" title="Approve">
                                                            <CheckCircle className="h-4 w-4" />
                                                        </button>
                                                        <button onClick={() => handleReject(req.id)} className="p-1.5 rounded-lg text-red-500 hover:bg-red-50" title="Reject">
                                                            <XCircle className="h-4 w-4" />
                                                        </button>
                                                    </>
                                                )}
                                                {req.status === 'pending' && (
                                                    <button onClick={() => { if (window.confirm('Delete?')) destroy(req.id); }} className="p-1.5 rounded-lg text-red-500 hover:bg-red-50" title="Delete">
                                                        <span className="h-4 w-4">×</span>
                                                    </button>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                                {requests.length === 0 && <tr><td colSpan={8} className="px-4 py-12 text-center text-gray-400">No leave requests found.</td></tr>}
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
    return (
        <div>
            <label className="mb-1 block text-sm font-medium text-gray-700">{label}</label>
            {children}
            {error && <p className="mt-1 text-xs text-red-600">{Array.isArray(error) ? error[0] : error}</p>}
        </div>
    );
}
