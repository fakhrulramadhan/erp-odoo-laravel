import { useState } from 'react';
import { Plus, Search, Eye, Edit2, Trash2, Send, XCircle } from 'lucide-react';
import { jobVacanciesApi } from '../../api/endpoints';
import { useCrudApi } from '../../hooks/useCrudApi';
import { StatusBadge, formatDate, formatCurrency } from '../../components/ui/StatusBadge';
import { Button } from '../../components/ui/Button';
import { Spinner } from '../../components/ui/Loader';

export default function JobVacanciesPage() {
    const {
        data: vacancies, loading, saving, error, setError,
        currentPage, lastPage, total,
        search, filterValues,
        fetchData, handlePageChange, handleSearchChange, handleFilterChange,
        store, update, destroy, performAction,
    } = useCrudApi(jobVacanciesApi);

    const [view, setView] = useState('list');
    const [selectedId, setSelectedId] = useState(null);
    const [form, setForm] = useState({});
    const [formErrors, setFormErrors] = useState({});

    const openForm = (vac = null) => {
        if (vac) {
            setForm({
                title: vac.title || '', department_id: vac.department_id || '',
                position_id: vac.position_id || '', employment_type: vac.employment_type || 'full_time',
                number_of_openings: vac.number_of_openings || 1, salary_min: vac.salary_min || '',
                salary_max: vac.salary_max || '', description: vac.description || '',
                requirements: vac.requirements || '', posting_date: vac.posting_date || '',
                closing_date: vac.closing_date || '',
            });
            setSelectedId(vac.id);
            setView('edit');
        } else {
            setForm({
                title: '', department_id: '', position_id: '', employment_type: 'full_time',
                number_of_openings: 1, salary_min: '', salary_max: '',
                description: '', requirements: '', posting_date: '', closing_date: '',
            });
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

    const handlePublish = async (id) => {
        if (!window.confirm('Publish this vacancy?')) return;
        await performAction(jobVacanciesApi.publish, id);
    };

    const handleClose = async (id) => {
        if (!window.confirm('Close this vacancy?')) return;
        await performAction(jobVacanciesApi.close, id);
    };

    if (view === 'create' || view === 'edit') {
        const f = form; const set = (k, v) => setForm({ ...form, [k]: v });
        return (
            <div className="mx-auto max-w-3xl space-y-6">
                <div className="flex items-center gap-3">
                    <button onClick={() => { setView('list'); setError(null); }} className="text-gray-500 hover:text-gray-700">← Back</button>
                    <h1 className="text-2xl font-bold text-gray-900">{view === 'edit' ? 'Edit' : 'New'} Job Vacancy</h1>
                </div>
                <form onSubmit={handleSubmit} className="space-y-4 rounded-xl border border-gray-200 bg-white p-6">
                    <Field label="Title *" error={formErrors.title}>
                        <input value={f.title} onChange={e => set('title', e.target.value)} className="input-field" required />
                    </Field>
                    <div className="grid grid-cols-2 gap-4">
                        <Field label="Department ID" error={formErrors.department_id}>
                            <input value={f.department_id} onChange={e => set('department_id', e.target.value)} className="input-field" />
                        </Field>
                        <Field label="Position ID" error={formErrors.position_id}>
                            <input value={f.position_id} onChange={e => set('position_id', e.target.value)} className="input-field" />
                        </Field>
                        <Field label="Employment Type" error={formErrors.employment_type}>
                            <select value={f.employment_type} onChange={e => set('employment_type', e.target.value)} className="input-field">
                                <option value="full_time">Full Time</option>
                                <option value="part_time">Part Time</option>
                                <option value="contract">Contract</option>
                                <option value="internship">Internship</option>
                            </select>
                        </Field>
                        <Field label="Openings" error={formErrors.number_of_openings}>
                            <input type="number" min="1" value={f.number_of_openings} onChange={e => set('number_of_openings', e.target.value)} className="input-field" />
                        </Field>
                        <Field label="Salary Min" error={formErrors.salary_min}>
                            <input type="number" step="0.01" value={f.salary_min} onChange={e => set('salary_min', e.target.value)} className="input-field" />
                        </Field>
                        <Field label="Salary Max" error={formErrors.salary_max}>
                            <input type="number" step="0.01" value={f.salary_max} onChange={e => set('salary_max', e.target.value)} className="input-field" />
                        </Field>
                        <Field label="Posting Date" error={formErrors.posting_date}>
                            <input type="date" value={f.posting_date} onChange={e => set('posting_date', e.target.value)} className="input-field" />
                        </Field>
                        <Field label="Closing Date" error={formErrors.closing_date}>
                            <input type="date" value={f.closing_date} onChange={e => set('closing_date', e.target.value)} className="input-field" />
                        </Field>
                    </div>
                    <Field label="Description" error={formErrors.description}>
                        <textarea value={f.description} onChange={e => set('description', e.target.value)} className="input-field" rows={3} />
                    </Field>
                    <Field label="Requirements" error={formErrors.requirements}>
                        <textarea value={f.requirements} onChange={e => set('requirements', e.target.value)} className="input-field" rows={3} />
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
                    <h1 className="text-2xl font-bold text-gray-900">Job Vacancies</h1>
                    <p className="text-sm text-gray-500">{total} vacancies total</p>
                </div>
                <Button onClick={() => openForm()} className="flex items-center gap-2">
                    <Plus className="h-4 w-4" /> New Vacancy
                </Button>
            </div>
            <div className="flex gap-3">
                <div className="relative flex-1">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <input type="text" value={search} onChange={e => handleSearchChange(e.target.value)}
                        placeholder="Search vacancies..." className="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" />
                </div>
                <select value={filterValues.status || ''} onChange={e => handleFilterChange({ ...filterValues, status: e.target.value })}
                    className="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                    <option value="closed">Closed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            {loading ? <div className="flex justify-center py-20"><Spinner /></div> : (
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Vacancy #</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Title</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Department</th>
                                    <th className="px-4 py-3 text-center font-medium text-gray-600">Openings</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Salary Range</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Closing</th>
                                    <th className="px-4 py-3 text-center font-medium text-gray-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {vacancies.map(v => (
                                    <tr key={v.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3 font-medium text-indigo-600">{v.vacancy_number}</td>
                                        <td className="px-4 py-3 text-gray-900">{v.title}</td>
                                        <td className="px-4 py-3 text-gray-600">{v.department?.name || '-'}</td>
                                        <td className="px-4 py-3 text-center text-gray-600">{v.number_of_openings}</td>
                                        <td className="px-4 py-3 text-gray-600">{v.salary_min && v.salary_max ? `${formatCurrency(v.salary_min)} - ${formatCurrency(v.salary_max)}` : '-'}</td>
                                        <td className="px-4 py-3"><StatusBadge status={v.status} /></td>
                                        <td className="px-4 py-3 text-gray-600">{formatDate(v.closing_date)}</td>
                                        <td className="px-4 py-3">
                                            <div className="flex items-center justify-center gap-1">
                                                {v.status === 'draft' && <button onClick={() => handlePublish(v.id)} className="p-1.5 rounded-lg text-green-500 hover:bg-green-50" title="Publish"><Send className="h-4 w-4" /></button>}
                                                {v.status === 'published' && <button onClick={() => handleClose(v.id)} className="p-1.5 rounded-lg text-orange-500 hover:bg-orange-50" title="Close"><XCircle className="h-4 w-4" /></button>}
                                                <button onClick={() => openForm(v)} className="p-1.5 rounded-lg text-blue-500 hover:bg-blue-50" title="Edit"><Edit2 className="h-4 w-4" /></button>
                                                <button onClick={() => { if (window.confirm('Delete?')) destroy(v.id); }} className="p-1.5 rounded-lg text-red-500 hover:bg-red-50" title="Delete"><Trash2 className="h-4 w-4" /></button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                                {vacancies.length === 0 && <tr><td colSpan={8} className="px-4 py-12 text-center text-gray-400">No vacancies found.</td></tr>}
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
