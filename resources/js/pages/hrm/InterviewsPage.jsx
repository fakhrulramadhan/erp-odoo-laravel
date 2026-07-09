import { useState } from 'react';
import { Plus, Search, Edit2, Trash2, MessageSquare } from 'lucide-react';
import { interviewsApi } from '../../api/endpoints';
import { useCrudApi } from '../../hooks/useCrudApi';
import { StatusBadge, formatDate } from '../../components/ui/StatusBadge';
import { Button } from '../../components/ui/Button';
import { Spinner } from '../../components/ui/Loader';

export default function InterviewsPage() {
    const {
        data: interviews, loading, saving, error, setError,
        currentPage, lastPage, total,
        search, filterValues,
        handlePageChange, handleSearchChange, handleFilterChange,
        store, update, destroy, performAction,
    } = useCrudApi(interviewsApi);

    const [view, setView] = useState('list');
    const [selectedId, setSelectedId] = useState(null);
    const [form, setForm] = useState({});
    const [formErrors, setFormErrors] = useState({});
    const [feedbackForm, setFeedbackForm] = useState({});

    const openForm = (item = null) => {
        if (item) {
            setForm({
                applicant_id: item.applicant_id || '', interviewer_name: item.interviewer_name || '',
                interview_type: item.interview_type || 'in_person', scheduled_at: item.scheduled_at || '',
                notes: item.notes || '',
            });
            setSelectedId(item.id);
            setView('edit');
        } else {
            setForm({ applicant_id: '', interviewer_name: '', interview_type: 'in_person', scheduled_at: '', notes: '' });
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

    const handleSubmitFeedback = async (id) => {
        setFeedbackForm({});
        setSelectedId(id);
        setView('feedback');
    };

    const handleFeedbackSubmit = async (e) => {
        e.preventDefault();
        const result = await performAction(() => interviewsApi.submitFeedback(selectedId, feedbackForm), selectedId);
        if (result) setView('list');
    };

    const set = (k, v) => setForm({ ...form, [k]: v });
    const setF = (k, v) => setFeedbackForm({ ...feedbackForm, [k]: v });

    if (view === 'feedback') {
        return (
            <div className="mx-auto max-w-xl space-y-6">
                <div className="flex items-center gap-3">
                    <button onClick={() => setView('list')} className="text-gray-500 hover:text-gray-700">← Back</button>
                    <h1 className="text-2xl font-bold text-gray-900">Submit Feedback</h1>
                </div>
                <form onSubmit={handleFeedbackSubmit} className="space-y-4 rounded-xl border border-gray-200 bg-white p-6">
                    <Field label="Rating (1-10)">
                        <input type="number" min="1" max="10" value={feedbackForm.rating || ''} onChange={e => setF('rating', e.target.value)} className="input-field" />
                    </Field>
                    <Field label="Result">
                        <select value={feedbackForm.result || ''} onChange={e => setF('result', e.target.value)} className="input-field">
                            <option value="">Select...</option>
                            <option value="pass">Pass</option>
                            <option value="fail">Fail</option>
                            <option value="on_hold">On Hold</option>
                        </select>
                    </Field>
                    <Field label="Feedback">
                        <textarea value={feedbackForm.feedback || ''} onChange={e => setF('feedback', e.target.value)} className="input-field" rows={4} />
                    </Field>
                    <div className="flex justify-end gap-3">
                        <Button type="button" variant="secondary" onClick={() => setView('list')}>Cancel</Button>
                        <Button type="submit" disabled={saving}>{saving ? 'Submitting...' : 'Submit Feedback'}</Button>
                    </div>
                </form>
            </div>
        );
    }

    if (view === 'create' || view === 'edit') {
        return (
            <div className="mx-auto max-w-2xl space-y-6">
                <div className="flex items-center gap-3">
                    <button onClick={() => { setView('list'); setError(null); }} className="text-gray-500 hover:text-gray-700">← Back</button>
                    <h1 className="text-2xl font-bold text-gray-900">{view === 'edit' ? 'Edit' : 'Schedule'} Interview</h1>
                </div>
                <form onSubmit={handleSubmit} className="space-y-4 rounded-xl border border-gray-200 bg-white p-6">
                    <div className="grid grid-cols-2 gap-4">
                        <Field label="Applicant ID *" error={formErrors.applicant_id}>
                            <input value={form.applicant_id} onChange={e => set('applicant_id', e.target.value)} className="input-field" required />
                        </Field>
                        <Field label="Interviewer *" error={formErrors.interviewer_name}>
                            <input value={form.interviewer_name} onChange={e => set('interviewer_name', e.target.value)} className="input-field" required />
                        </Field>
                        <Field label="Type" error={formErrors.interview_type}>
                            <select value={form.interview_type} onChange={e => set('interview_type', e.target.value)} className="input-field">
                                <option value="in_person">In Person</option>
                                <option value="phone">Phone</option>
                                <option value="video">Video</option>
                            </select>
                        </Field>
                        <Field label="Scheduled At *" error={formErrors.scheduled_at}>
                            <input type="datetime-local" value={form.scheduled_at} onChange={e => set('scheduled_at', e.target.value)} className="input-field" required />
                        </Field>
                    </div>
                    <Field label="Notes" error={formErrors.notes}>
                        <textarea value={form.notes} onChange={e => set('notes', e.target.value)} className="input-field" rows={3} />
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
                    <h1 className="text-2xl font-bold text-gray-900">Interviews</h1>
                    <p className="text-sm text-gray-500">{total} interviews total</p>
                </div>
                <Button onClick={() => openForm()} className="flex items-center gap-2">
                    <Plus className="h-4 w-4" /> Schedule Interview
                </Button>
            </div>
            <div className="relative">
                <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                <input type="text" value={search} onChange={e => handleSearchChange(e.target.value)}
                    placeholder="Search interviews..." className="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" />
            </div>
            {loading ? <div className="flex justify-center py-20"><Spinner /></div> : (
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Interview #</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Applicant</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Interviewer</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Type</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Scheduled</th>
                                    <th className="px-4 py-3 text-center font-medium text-gray-600">Rating</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                                    <th className="px-4 py-3 text-center font-medium text-gray-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {interviews.map(int => (
                                    <tr key={int.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3 font-medium text-indigo-600">{int.interview_number}</td>
                                        <td className="px-4 py-3 text-gray-900">{int.applicant?.name || '-'}</td>
                                        <td className="px-4 py-3 text-gray-600">{int.interviewer_name}</td>
                                        <td className="px-4 py-3 text-gray-600 capitalize">{int.interview_type?.replace(/_/g, ' ')}</td>
                                        <td className="px-4 py-3 text-gray-600">{formatDate(int.scheduled_at)}</td>
                                        <td className="px-4 py-3 text-center text-gray-600">{int.rating || '-'}</td>
                                        <td className="px-4 py-3"><StatusBadge status={int.status} /></td>
                                        <td className="px-4 py-3">
                                            <div className="flex items-center justify-center gap-1">
                                                {!['completed', 'cancelled'].includes(int.status) && <button onClick={() => handleSubmitFeedback(int.id)} className="p-1.5 rounded-lg text-green-500 hover:bg-green-50" title="Submit Feedback"><MessageSquare className="h-4 w-4" /></button>}
                                                <button onClick={() => openForm(int)} className="p-1.5 rounded-lg text-blue-500 hover:bg-blue-50" title="Edit"><Edit2 className="h-4 w-4" /></button>
                                                <button onClick={() => { if (window.confirm('Delete?')) destroy(int.id); }} className="p-1.5 rounded-lg text-red-500 hover:bg-red-50" title="Delete"><Trash2 className="h-4 w-4" /></button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                                {interviews.length === 0 && <tr><td colSpan={8} className="px-4 py-12 text-center text-gray-400">No interviews found.</td></tr>}
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
