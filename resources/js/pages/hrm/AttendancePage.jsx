import { useState } from 'react';
import { Plus, Search, Eye, Edit2, Trash2, Clock } from 'lucide-react';
import { attendanceApi } from '../../api/endpoints';
import { useCrudApi } from '../../hooks/useCrudApi';
import { StatusBadge, formatDate } from '../../components/ui/StatusBadge';
import { Button } from '../../components/ui/Button';
import { Spinner } from '../../components/ui/Loader';

export default function AttendancePage() {
    const {
        data: records, loading, saving, error, setError,
        currentPage, lastPage, total,
        search, filterValues,
        handlePageChange, handleSearchChange, handleFilterChange,
        store, update, destroy,
    } = useCrudApi(attendanceApi);

    const [view, setView] = useState('list');
    const [selectedId, setSelectedId] = useState(null);
    const [form, setForm] = useState({});
    const [formErrors, setFormErrors] = useState({});

    const openForm = (rec = null) => {
        if (rec) {
            setForm({
                employee_id: rec.employee_id || '', attendance_date: rec.attendance_date || '',
                check_in: rec.check_in ? rec.check_in.slice(11, 16) : '', check_out: rec.check_out ? rec.check_out.slice(11, 16) : '',
                status: rec.status || '', notes: rec.notes || '',
            });
            setSelectedId(rec.id);
            setView('edit');
        } else {
            setForm({ employee_id: '', attendance_date: new Date().toISOString().slice(0, 10), check_in: '', check_out: '', status: 'draft', notes: '' });
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

    const handleCheckIn = async () => {
        try {
            await attendanceApi.checkIn({ attendance_date: new Date().toISOString().slice(0, 10) });
            window.location.reload();
        } catch {}
    };

    const handleCheckOut = async (id) => {
        try {
            await attendanceApi.checkOut(id);
            window.location.reload();
        } catch {}
    };

    if (view === 'create' || view === 'edit') {
        const f = form; const set = (k, v) => setForm({ ...form, [k]: v });
        return (
            <div className="mx-auto max-w-2xl space-y-6">
                <div className="flex items-center gap-3">
                    <button onClick={() => { setView('list'); setError(null); }} className="text-gray-500 hover:text-gray-700">← Back</button>
                    <h1 className="text-2xl font-bold text-gray-900">{view === 'edit' ? 'Edit' : 'New'} Attendance</h1>
                </div>
                <form onSubmit={handleSubmit} className="space-y-4 rounded-xl border border-gray-200 bg-white p-6">
                    <div className="grid grid-cols-2 gap-4">
                        <Field label="Employee ID *" error={formErrors.employee_id}>
                            <input value={f.employee_id} onChange={e => set('employee_id', e.target.value)} className="input-field" required />
                        </Field>
                        <Field label="Date *" error={formErrors.attendance_date}>
                            <input type="date" value={f.attendance_date} onChange={e => set('attendance_date', e.target.value)} className="input-field" required />
                        </Field>
                        <Field label="Check In" error={formErrors.check_in}>
                            <input type="time" value={f.check_in} onChange={e => set('check_in', e.target.value)} className="input-field" />
                        </Field>
                        <Field label="Check Out" error={formErrors.check_out}>
                            <input type="time" value={f.check_out} onChange={e => set('check_out', e.target.value)} className="input-field" />
                        </Field>
                    </div>
                    <Field label="Notes" error={formErrors.notes}>
                        <textarea value={f.notes} onChange={e => set('notes', e.target.value)} className="input-field" rows={2} />
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
                    <h1 className="text-2xl font-bold text-gray-900">Attendance</h1>
                    <p className="text-sm text-gray-500">{total} records total</p>
                </div>
                <div className="flex gap-2">
                    <Button variant="secondary" onClick={handleCheckIn} className="flex items-center gap-2">
                        <Clock className="h-4 w-4" /> Check In
                    </Button>
                    <Button onClick={() => openForm()} className="flex items-center gap-2">
                        <Plus className="h-4 w-4" /> New Record
                    </Button>
                </div>
            </div>

            <div className="flex gap-3">
                <div className="relative flex-1">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <input type="text" value={search} onChange={e => handleSearchChange(e.target.value)}
                        placeholder="Search attendance..." className="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" />
                </div>
                <select value={filterValues.status || ''} onChange={e => handleFilterChange({ ...filterValues, status: e.target.value })}
                    className="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">All Status</option>
                    <option value="checked_in">Checked In</option>
                    <option value="checked_out">Checked Out</option>
                    <option value="absent">Absent</option>
                    <option value="late">Late</option>
                    <option value="on_leave">On Leave</option>
                </select>
            </div>

            {loading ? <div className="flex justify-center py-20"><Spinner /></div> : (
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Employee</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Date</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Check In</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Check Out</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">Work Hours</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">Late (min)</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                                    <th className="px-4 py-3 text-center font-medium text-gray-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {records.map(rec => (
                                    <tr key={rec.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3 font-medium text-gray-900">{rec.employee?.full_name || rec.employee?.first_name || '-'}</td>
                                        <td className="px-4 py-3 text-gray-600">{formatDate(rec.attendance_date)}</td>
                                        <td className="px-4 py-3 text-gray-600">{rec.check_in ? new Date(rec.check_in).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) : '-'}</td>
                                        <td className="px-4 py-3 text-gray-600">{rec.check_out ? new Date(rec.check_out).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) : '-'}</td>
                                        <td className="px-4 py-3 text-right text-gray-600">{rec.work_hours || '-'}</td>
                                        <td className="px-4 py-3 text-right text-gray-600">{rec.late_minutes || 0}</td>
                                        <td className="px-4 py-3"><StatusBadge status={rec.status} /></td>
                                        <td className="px-4 py-3">
                                            <div className="flex items-center justify-center gap-1">
                                                {rec.status === 'checked_in' && (
                                                    <button onClick={() => handleCheckOut(rec.id)} className="p-1.5 rounded-lg text-orange-500 hover:bg-orange-50" title="Check Out">
                                                        <Clock className="h-4 w-4" />
                                                    </button>
                                                )}
                                                <button onClick={() => openForm(rec)} className="p-1.5 rounded-lg text-blue-500 hover:bg-blue-50" title="Edit">
                                                    <Edit2 className="h-4 w-4" />
                                                </button>
                                                <button onClick={() => { if (window.confirm('Delete?')) destroy(rec.id); }} className="p-1.5 rounded-lg text-red-500 hover:bg-red-50" title="Delete">
                                                    <Trash2 className="h-4 w-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                                {records.length === 0 && <tr><td colSpan={8} className="px-4 py-12 text-center text-gray-400">No attendance records found.</td></tr>}
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
