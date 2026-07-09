import { useState } from 'react';
import { Plus, Search, Edit2, Trash2, ChevronRight } from 'lucide-react';
import { applicantsApi } from '../../api/endpoints';
import { useCrudApi } from '../../hooks/useCrudApi';
import { StatusBadge, formatDate } from '../../components/ui/StatusBadge';
import { Button } from '../../components/ui/Button';
import { Spinner } from '../../components/ui/Loader';

const STAGE_OPTIONS = [
    { value: '', label: 'All Stages' },
    { value: 'new', label: 'New' },
    { value: 'screening', label: 'Screening' },
    { value: 'interview', label: 'Interview' },
    { value: 'technical_test', label: 'Technical Test' },
    { value: 'offer', label: 'Offer' },
    { value: 'hired', label: 'Hired' },
    { value: 'rejected', label: 'Rejected' },
];

const STAGE_COLORS = {
    new: 'bg-gray-100 text-gray-700',
    screening: 'bg-blue-100 text-blue-700',
    interview: 'bg-purple-100 text-purple-700',
    technical_test: 'bg-yellow-100 text-yellow-700',
    offer: 'bg-green-100 text-green-700',
    hired: 'bg-emerald-100 text-emerald-700',
    rejected: 'bg-red-100 text-red-700',
};

export default function ApplicantsPage() {
    const {
        data: applicants, loading, saving, error, setError,
        currentPage, lastPage, total,
        search, filterValues,
        fetchData, handlePageChange, handleSearchChange, handleFilterChange,
        store, update, destroy, performAction,
    } = useCrudApi(applicantsApi);

    const [view, setView] = useState('list');
    const [selectedId, setSelectedId] = useState(null);
    const [form, setForm] = useState({});
    const [formErrors, setFormErrors] = useState({});

    const openForm = (app = null) => {
        if (app) {
            setForm({
                job_vacancy_id: app.job_vacancy_id || '', name: app.name || '',
                email: app.email || '', phone: app.phone || '', address: app.address || '',
                date_of_birth: app.date_of_birth || '', stage: app.stage || 'new',
                score: app.score || '', notes: app.notes || '', assigned_to: app.assigned_to || '',
            });
            setSelectedId(app.id);
            setView('edit');
        } else {
            setForm({ job_vacancy_id: '', name: '', email: '', phone: '', address: '', date_of_birth: '', stage: 'new', score: '', notes: '', assigned_to: '' });
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

    const handleUpdateStage = async (id, stage) => {
        await performAction(() => applicantsApi.updateStage(id, { stage }), id);
    };

    if (view === 'create' || view === 'edit') {
        const f = form; const set = (k, v) => setForm({ ...form, [k]: v });
        return (
            <div className="mx-auto max-w-3xl space-y-6">
                <div className="flex items-center gap-3">
                    <button onClick={() => { setView('list'); setError(null); }} className="text-gray-500 hover:text-gray-700">← Back</button>
                    <h1 className="text-2xl font-bold text-gray-900">{view === 'edit' ? 'Edit' : 'New'} Applicant</h1>
                </div>
                <form onSubmit={handleSubmit} className="space-y-4 rounded-xl border border-gray-200 bg-white p-6">
                    <div className="grid grid-cols-2 gap-4">
                        <Field label="Name *" error={formErrors.name}>
                            <input value={f.name} onChange={e => set('name', e.target.value)} className="input-field" required />
                        </Field>
                        <Field label="Email" error={formErrors.email}>
                            <input type="email" value={f.email} onChange={e => set('email', e.target.value)} className="input-field" />
                        </Field>
                        <Field label="Phone" error={formErrors.phone}>
                            <input value={f.phone} onChange={e => set('phone', e.target.value)} className="input-field" />
                        </Field>
                        <Field label="Job Vacancy ID" error={formErrors.job_vacancy_id}>
                            <input value={f.job_vacancy_id} onChange={e => set('job_vacancy_id', e.target.value)} className="input-field" />
                        </Field>
                        <Field label="Date of Birth" error={formErrors.date_of_birth}>
                            <input type="date" value={f.date_of_birth} onChange={e => set('date_of_birth', e.target.value)} className="input-field" />
                        </Field>
                        <Field label="Stage" error={formErrors.stage}>
                            <select value={f.stage} onChange={e => set('stage', e.target.value)} className="input-field">
                                {STAGE_OPTIONS.filter(o => o.value).map(o => <option key={o.value} value={o.value}>{o.label}</option>)}
                            </select>
                        </Field>
                    </div>
                    <Field label="Address" error={formErrors.address}>
                        <textarea value={f.address} onChange={e => set('address', e.target.value)} className="input-field" rows={2} />
                    </Field>
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
                    <h1 className="text-2xl font-bold text-gray-900">Applicants</h1>
                    <p className="text-sm text-gray-500">{total} applicants total</p>
                </div>
                <Button onClick={() => openForm()} className="flex items-center gap-2">
                    <Plus className="h-4 w-4" /> New Applicant
                </Button>
            </div>
            <div className="flex gap-3">
                <div className="relative flex-1">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <input type="text" value={search} onChange={e => handleSearchChange(e.target.value)}
                        placeholder="Search applicants..." className="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" />
                </div>
                <select value={filterValues.stage || ''} onChange={e => handleFilterChange({ ...filterValues, stage: e.target.value })}
                    className="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    {STAGE_OPTIONS.map(o => <option key={o.value} value={o.value}>{o.label}</option>)}
                </select>
            </div>
            {loading ? <div className="flex justify-center py-20"><Spinner /></div> : (
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Applicant #</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Name</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Vacancy</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Stage</th>
                                    <th className="px-4 py-3 text-center font-medium text-gray-600">Score</th>
                                    <th className="px-4 py-3 text-center font-medium text-gray-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {applicants.map(app => (
                                    <tr key={app.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3 font-medium text-indigo-600">{app.applicant_number}</td>
                                        <td className="px-4 py-3 text-gray-900">{app.name}</td>
                                        <td className="px-4 py-3 text-gray-600">{app.job_vacancy?.title || '-'}</td>
                                        <td className="px-4 py-3">
                                            <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${STAGE_COLORS[app.stage] || 'bg-gray-100 text-gray-700'}`}>
                                                {app.stage?.replace(/_/g, ' ')}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 text-center text-gray-600">{app.score || '-'}</td>
                                        <td className="px-4 py-3">
                                            <div className="flex items-center justify-center gap-1">
                                                <button onClick={() => openForm(app)} className="p-1.5 rounded-lg text-blue-500 hover:bg-blue-50" title="Edit"><Edit2 className="h-4 w-4" /></button>
                                                <button onClick={() => { if (window.confirm('Delete?')) destroy(app.id); }} className="p-1.5 rounded-lg text-red-500 hover:bg-red-50" title="Delete"><Trash2 className="h-4 w-4" /></button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                                {applicants.length === 0 && <tr><td colSpan={6} className="px-4 py-12 text-center text-gray-400">No applicants found.</td></tr>}
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
