import { useState } from 'react';
import { Plus, Search, Edit2, Trash2 } from 'lucide-react';
import { useCrudApi } from '../../hooks/useCrudApi';
import { StatusBadge, formatDate } from '../../components/ui/StatusBadge';
import { Button } from '../../components/ui/Button';
import { Spinner } from '../../components/ui/Loader';

export default function HrmSimplePage({ title, api, columns, formFields, filterOptions = null }) {
    const {
        data, loading, saving, error, setError,
        currentPage, lastPage, total,
        search, filterValues,
        handlePageChange, handleSearchChange, handleFilterChange,
        store, update, destroy,
    } = useCrudApi(api);

    const [view, setView] = useState('list');
    const [selectedId, setSelectedId] = useState(null);
    const [form, setForm] = useState({});
    const [formErrors, setFormErrors] = useState({});

    const openForm = (item = null) => {
        if (item) {
            const f = {};
            formFields.forEach(field => { f[field.key] = item[field.key] ?? ''; });
            setForm(f);
            setSelectedId(item.id);
            setView('edit');
        } else {
            const f = {};
            formFields.forEach(field => { f[field.key] = field.default ?? ''; });
            setForm(f);
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

    const set = (k, v) => setForm({ ...form, [k]: v });

    if (view === 'create' || view === 'edit') {
        return (
            <div className="mx-auto max-w-2xl space-y-6">
                <div className="flex items-center gap-3">
                    <button onClick={() => { setView('list'); setError(null); }} className="text-gray-500 hover:text-gray-700">← Back</button>
                    <h1 className="text-2xl font-bold text-gray-900">{view === 'edit' ? 'Edit' : 'New'} {title}</h1>
                </div>
                {error && typeof error === 'string' && <div className="rounded-lg bg-red-50 p-4 text-red-700">{error}</div>}
                <form onSubmit={handleSubmit} className="space-y-4 rounded-xl border border-gray-200 bg-white p-6">
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        {formFields.map(field => (
                            <div key={field.key} className={field.fullWidth ? 'md:col-span-2' : ''}>
                                <label className="mb-1 block text-sm font-medium text-gray-700">{field.label}{field.required ? ' *' : ''}</label>
                                {field.type === 'select' ? (
                                    <select value={form[field.key] || ''} onChange={e => set(field.key, e.target.value)} className="input-field" required={field.required}>
                                        <option value="">Select...</option>
                                        {(field.options || []).map(o => <option key={o.value} value={o.value}>{o.label}</option>)}
                                    </select>
                                ) : field.type === 'textarea' ? (
                                    <textarea value={form[field.key] || ''} onChange={e => set(field.key, e.target.value)} className="input-field" rows={field.rows || 3} required={field.required} />
                                ) : field.type === 'checkbox' ? (
                                    <label className="flex items-center gap-2">
                                        <input type="checkbox" checked={!!form[field.key]} onChange={e => set(field.key, e.target.checked)} className="rounded" />
                                        <span className="text-sm text-gray-600">{field.checkLabel || 'Yes'}</span>
                                    </label>
                                ) : (
                                    <input type={field.type || 'text'} value={form[field.key] || ''} onChange={e => set(field.key, e.target.value)}
                                        className="input-field" required={field.required} step={field.step} min={field.min} />
                                )}
                                {formErrors[field.key] && <p className="mt-1 text-xs text-red-600">{Array.isArray(formErrors[field.key]) ? formErrors[field.key][0] : formErrors[field.key]}</p>}
                            </div>
                        ))}
                    </div>
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
                    <h1 className="text-2xl font-bold text-gray-900">{title}</h1>
                    <p className="text-sm text-gray-500">{total} records total</p>
                </div>
                <Button onClick={() => openForm()} className="flex items-center gap-2">
                    <Plus className="h-4 w-4" /> New {title.replace(/s$/, '')}
                </Button>
            </div>
            <div className="flex gap-3">
                <div className="relative flex-1">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <input type="text" value={search} onChange={e => handleSearchChange(e.target.value)}
                        placeholder={`Search ${title.toLowerCase()}...`} className="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" />
                </div>
                {filterOptions && (
                    <select value={filterValues[filterOptions.key] || ''} onChange={e => handleFilterChange({ ...filterValues, [filterOptions.key]: e.target.value })}
                        className="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">{filterOptions.allLabel || 'All'}</option>
                        {filterOptions.options.map(o => <option key={o.value} value={o.value}>{o.label}</option>)}
                    </select>
                )}
            </div>
            {loading ? <div className="flex justify-center py-20"><Spinner /></div> : (
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    {columns.map(col => (
                                        <th key={col.key} className={`px-4 py-3 text-${col.align || 'left'} font-medium text-gray-600`}>{col.label}</th>
                                    ))}
                                    <th className="px-4 py-3 text-center font-medium text-gray-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {data.map(item => (
                                    <tr key={item.id} className="hover:bg-gray-50">
                                        {columns.map(col => (
                                            <td key={col.key} className={`px-4 py-3 text-${col.align || 'left'} ${col.fontMedium ? 'font-medium text-gray-900' : 'text-gray-600'}`}>
                                                {col.render ? col.render(item) : col.type === 'status' ? <StatusBadge status={item[col.key]} /> : col.type === 'date' ? formatDate(item[col.key]) : col.type === 'currency' ? `Rp ${Number(item[col.key] || 0).toLocaleString('id-ID')}` : (item[col.key] ?? '-')}
                                            </td>
                                        ))}
                                        <td className="px-4 py-3">
                                            <div className="flex items-center justify-center gap-1">
                                                <button onClick={() => openForm(item)} className="p-1.5 rounded-lg text-blue-500 hover:bg-blue-50" title="Edit"><Edit2 className="h-4 w-4" /></button>
                                                <button onClick={() => { if (window.confirm('Delete?')) destroy(item.id); }} className="p-1.5 rounded-lg text-red-500 hover:bg-red-50" title="Delete"><Trash2 className="h-4 w-4" /></button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                                {data.length === 0 && <tr><td colSpan={columns.length + 1} className="px-4 py-12 text-center text-gray-400">No records found.</td></tr>}
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
