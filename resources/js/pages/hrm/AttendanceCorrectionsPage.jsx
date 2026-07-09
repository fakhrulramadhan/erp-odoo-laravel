import { useState } from 'react';
import { Plus, Search, Edit2, Trash2, CheckCircle, XCircle } from 'lucide-react';
import { attendanceCorrectionsApi } from '../../api/endpoints';
import { useCrudApi } from '../../hooks/useCrudApi';
import { StatusBadge, formatDate } from '../../components/ui/StatusBadge';
import { Button } from '../../components/ui/Button';
import { Spinner } from '../../components/ui/Loader';

export default function AttendanceCorrectionsPage() {
    const {
        data: corrections, loading, saving, error, setError,
        currentPage, lastPage, total,
        search, filterValues,
        handlePageChange, handleSearchChange, handleFilterChange,
        store, update, destroy, performAction,
    } = useCrudApi(attendanceCorrectionsApi);

    const [view, setView] = useState('list');
    const [selectedId, setSelectedId] = useState(null);
    const [form, setForm] = useState({});
    const [formErrors, setFormErrors] = useState({});

    const openForm = (item = null) => {
        if (item) {
            setForm({
                attendance_id: item.attendance_id || '', correction_date: item.correction_date || '',
                clock_in_time: item.clock_in_time || '', clock_out_time: item.clock_out_time || '',
                reason: item.reason || '',
            });
            setSelectedId(item.id);
            setView('edit');
        } else {
            setForm({ attendance_id: '', correction_date: '', clock_in_time: '', clock_out_time: '', reason: '' });
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
        if (!window.confirm('Approve this correction?')) return;
        await performAction(attendanceCorrectionsApi.approve, id);
    };

    const handleReject = async (id) => {
        if (!window.confirm('Reject this correction?')) return;
        await performAction(attendanceCorrectionsApi.reject, id);
    };

    const set = (k, v) => setForm({ ...form, [k]: v });

    if (view === 'create' || view === 'edit') {
        return (
            <div className="mx-auto max-w-2xl space-y-6">
                <div className="flex items-center gap-3">
                    <button onClick={() => { setView('list'); setError(null); }} className="text-gray-500 hover:text-gray-700">← Back</button>
                    <h1 className="text-2xl font-bold text-gray-900">{view === 'edit' ? 'Edit' : 'New'} Correction</h1>
                </div>
                <form onSubmit={handleSubmit} className="space-y-4 rounded-xl border border-gray-200 bg-white p-6">
                    <div className="grid grid-cols-2 gap-4">
                        <Field label="Attendance ID *" error={formErrors.attendance_id}>
                            <input value={form.attendance_id} onChange={e => set('attendance_id', e.target.value)} className="input-field" required />
                        </Field>
                        <Field label="Correction Date *" error={formErrors.correction_date}>
                            <input type="date" value={form.correction_date} onChange={e => set('correction_date', e.target.value)} className="input-field" required />
                        </Field>
                        <Field label="Clock In Time" error={formErrors.clock_in_time}>
                            <input type="time" value={form.clock_in_time} onChange={e => set('clock_in_time', e.target.value)} className="input-field" />
                        </Field>
                        <Field label="Clock Out Time" error={formErrors.clock_out_time}>
                            <input type="time" value={form.clock_out_time} onChange={e => set('clock_out_time', e.target.value)} className="input-field" />
                        </Field>
                    </div>
                    <Field label="Reason *" error={formErrors.reason}>
                        <textarea value={form.reason} onChange={e => set('reason', e.target.value)} className="input-field" rows={3} required />
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
                    <h1 className="text-2xl font-bold text-gray-900">Attendance Corrections</h1>
                    <p className="text-sm text-gray-500">{total} corrections total</p>
                </div>
                <Button onClick={() => openForm()} className="flex items-center gap-2">
                    <Plus className="h-4 w-4" /> New Correction
                </Button>
            </div>
            <div className="flex gap-3">
                <div className="relative flex-1">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <input type="text" value={search} onChange={e => handleSearchChange(e.target.value)}
                        placeholder="Search corrections..." className="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" />
                </div>
                <select value={filterValues.status || ''} onChange={e => handleFilterChange({ ...filterValues, status: e.target.value })}
                    className="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            {loading ? <div className="flex justify-center py-20"><Spinner /></div> : (
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Correction #</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Employee</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Date</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Corrected In</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Corrected Out</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Reason</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                                    <th className="px-4 py-3 text-center font-medium text-gray-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {corrections.map(c => (
                                    <tr key={c.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3 font-medium text-indigo-600">{c.correction_number}</td>
                                        <td className="px-4 py-3 text-gray-900">{c.employee?.full_name || '-'}</td>
                                        <td className="px-4 py-3 text-gray-600">{formatDate(c.correction_date)}</td>
                                        <td className="px-4 py-3 text-gray-600">{c.clock_in_time || '-'}</td>
                                        <td className="px-4 py-3 text-gray-600">{c.clock_out_time || '-'}</td>
                                        <td className="px-4 py-3 text-gray-600 truncate max-w-40">{c.reason || '-'}</td>
                                        <td className="px-4 py-3"><StatusBadge status={c.status} /></td>
                                        <td className="px-4 py-3">
                                            <div className="flex items-center justify-center gap-1">
                                                {c.status === 'pending' && <>
                                                    <button onClick={() => handleApprove(c.id)} className="p-1.5 rounded-lg text-green-500 hover:bg-green-50" title="Approve"><CheckCircle className="h-4 w-4" /></button>
                                                    <button onClick={() => handleReject(c.id)} className="p-1.5 rounded-lg text-red-500 hover:bg-red-50" title="Reject"><XCircle className="h-4 w-4" /></button>
                                                </>}
                                                <button onClick={() => openForm(c)} className="p-1.5 rounded-lg text-blue-500 hover:bg-blue-50" title="Edit"><Edit2 className="h-4 w-4" /></button>
                                                <button onClick={() => { if (window.confirm('Delete?')) destroy(c.id); }} className="p-1.5 rounded-lg text-red-500 hover:bg-red-50" title="Delete"><Trash2 className="h-4 w-4" /></button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                                {corrections.length === 0 && <tr><td colSpan={8} className="px-4 py-12 text-center text-gray-400">No corrections found.</td></tr>}
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
