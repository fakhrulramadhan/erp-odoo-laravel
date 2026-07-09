import { useState } from 'react';
import { Plus, Search, Eye, Edit2, Trash2, ArrowLeft, Package } from 'lucide-react';
import { assetCategoriesApi } from '../../api/endpoints';
import { useCrudApi } from '../../hooks/useCrudApi';
import { Button } from '../../components/ui/Button';
import { Spinner } from '../../components/ui/Loader';

export default function AssetCategoriesPage() {
    const {
        data: categories, loading, saving, error, setError,
        currentPage, lastPage, total,
        search, fetchData, handlePageChange, handleSearchChange,
        store, update, destroy,
    } = useCrudApi(assetCategoriesApi);

    const [view, setView] = useState('list');
    const [selectedId, setSelectedId] = useState(null);
    const [detail, setDetail] = useState(null);

    const loadDetail = async (id) => {
        try { const res = await assetCategoriesApi.show(id); setDetail(res.data.data); setSelectedId(id); setView('detail'); } catch {}
    };

    const handleDelete = async (id) => {
        if (window.confirm('Delete this category?')) { const ok = await destroy(id); if (ok && view === 'detail') setView('list'); }
    };

    if (view === 'create' || view === 'edit') {
        return <AssetCategoryForm isEdit={view === 'edit'} itemId={selectedId}
            onBack={() => { setView(selectedId ? 'detail' : 'list'); setError(null); }}
            onSave={async (data) => { let ok = view === 'edit' ? await update(selectedId, data) : await store(data); if (ok) { view === 'edit' ? loadDetail(selectedId) : setView('list'); } return ok; }}
            saving={saving} error={error} />;
    }

    if (view === 'detail' && detail) {
        return (
            <div className="space-y-6">
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div className="flex items-center gap-3">
                        <button onClick={() => { setView('list'); setDetail(null); }} className="p-2 rounded-lg hover:bg-gray-100"><ArrowLeft className="h-5 w-5" /></button>
                        <div><h1 className="text-2xl font-bold text-gray-900">{detail.name}</h1><p className="text-sm text-gray-500">{detail.code}</p></div>
                    </div>
                    <div className="flex gap-2">
                        <Button onClick={() => { setSelectedId(detail.id); setView('edit'); }} className="flex items-center gap-2 bg-blue-50 text-blue-700 hover:bg-blue-100"><Edit2 className="h-4 w-4" /> Edit</Button>
                        <Button onClick={() => handleDelete(detail.id)} className="flex items-center gap-2 bg-red-50 text-red-700 hover:bg-red-100"><Trash2 className="h-4 w-4" /> Delete</Button>
                    </div>
                </div>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <InfoCard label="Code" value={detail.code} />
                    <InfoCard label="Name" value={detail.name} />
                    <InfoCard label="Depreciation Method" value={detail.depreciation_method || '-'} />
                    <InfoCard label="Useful Life (months)" value={detail.useful_life_months || '-'} />
                    <InfoCard label="Description" value={detail.description || '-'} />
                </div>
            </div>
        );
    }

    return (
        <div className="space-y-6">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div><h1 className="text-2xl font-bold text-gray-900">Asset Categories</h1><p className="text-sm text-gray-500">{total} categories total</p></div>
                <Button onClick={() => { setSelectedId(null); setView('create'); }} className="flex items-center gap-2"><Plus className="h-4 w-4" /> New Category</Button>
            </div>
            <div className="relative max-w-md"><Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" /><input type="text" value={search} onChange={(e) => handleSearchChange(e.target.value)} placeholder="Search..." className="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" /></div>
            {loading ? <div className="flex justify-center py-20"><Spinner /></div> : (
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 border-b border-gray-200"><tr>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Code</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Name</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Depreciation Method</th>
                                <th className="px-4 py-3 text-right font-medium text-gray-600">Useful Life</th>
                                <th className="px-4 py-3 text-center font-medium text-gray-600">Actions</th>
                            </tr></thead>
                            <tbody className="divide-y divide-gray-100">
                                {categories.map((c) => (
                                    <tr key={c.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3"><button onClick={() => loadDetail(c.id)} className="font-medium text-indigo-600 hover:text-indigo-800">{c.code}</button></td>
                                        <td className="px-4 py-3 text-gray-800">{c.name}</td>
                                        <td className="px-4 py-3 text-gray-600 capitalize">{c.depreciation_method?.replace(/_/g, ' ') || '-'}</td>
                                        <td className="px-4 py-3 text-right">{c.useful_life_months || '-'}</td>
                                        <td className="px-4 py-3"><div className="flex items-center justify-center gap-1">
                                            <button onClick={() => loadDetail(c.id)} className="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100"><Eye className="h-4 w-4" /></button>
                                            <button onClick={() => { setSelectedId(c.id); setView('edit'); }} className="p-1.5 rounded-lg text-blue-500 hover:bg-blue-50"><Edit2 className="h-4 w-4" /></button>
                                            <button onClick={() => handleDelete(c.id)} className="p-1.5 rounded-lg text-red-500 hover:bg-red-50"><Trash2 className="h-4 w-4" /></button>
                                        </div></td>
                                    </tr>
                                ))}
                                {categories.length === 0 && <tr><td colSpan={5} className="px-4 py-12 text-center text-gray-400">No categories found.</td></tr>}
                            </tbody>
                        </table>
                    </div>
                </div>
            )}
        </div>
    );
}

function InfoCard({ label, value }) { return <div className="bg-white rounded-xl border border-gray-200 p-4"><p className="text-xs text-gray-500 uppercase tracking-wide mb-1">{label}</p><p className="text-sm font-medium text-gray-900">{value}</p></div>; }

function AssetCategoryForm({ isEdit, itemId, onBack, onSave, saving, error }) {
    const [form, setForm] = useState({ name: '', code: '', description: '', depreciation_method: 'straight_line', useful_life_months: 60 });
    const [loadingForm, setLoadingForm] = useState(isEdit);

    useState(() => {
        if (isEdit && itemId) {
            assetCategoriesApi.show(itemId).then(res => {
                const d = res.data.data;
                setForm({ name: d.name, code: d.code, description: d.description || '', depreciation_method: d.depreciation_method || 'straight_line', useful_life_months: d.useful_life_months || 60 });
                setLoadingForm(false);
            }).catch(() => setLoadingForm(false));
        }
    });

    if (loadingForm) return <div className="flex justify-center py-20"><Spinner /></div>;
    const errorMessages = error?.errors ? Object.values(error.errors).flat() : [];

    return (
        <div className="space-y-6">
            <div className="flex items-center gap-3">
                <button onClick={onBack} className="p-2 rounded-lg hover:bg-gray-100"><ArrowLeft className="h-5 w-5" /></button>
                <h1 className="text-2xl font-bold text-gray-900">{isEdit ? 'Edit Category' : 'New Category'}</h1>
            </div>
            {error && <div className="rounded-lg bg-red-50 p-4 text-red-700 text-sm"><p className="font-medium">{error.message}</p>{errorMessages.length > 0 && <ul className="mt-1 list-disc list-inside">{errorMessages.map((m, i) => <li key={i}>{m}</li>)}</ul>}</div>}
            <form onSubmit={async (e) => { e.preventDefault(); await onSave(form); }} className="space-y-6">
                <div className="bg-white rounded-xl border border-gray-200 p-6">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Name *</label><input type="text" value={form.name} onChange={e => setForm(p => ({ ...p, name: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required /></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Code</label><input type="text" value={form.code} onChange={e => setForm(p => ({ ...p, code: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Depreciation Method</label><select value={form.depreciation_method} onChange={e => setForm(p => ({ ...p, depreciation_method: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><option value="straight_line">Straight Line</option><option value="declining_balance">Declining Balance</option><option value="double_declining_balance">Double Declining Balance</option></select></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Useful Life (months)</label><input type="number" min="1" value={form.useful_life_months} onChange={e => setForm(p => ({ ...p, useful_life_months: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
                        <div className="md:col-span-2"><label className="block text-sm font-medium text-gray-700 mb-1">Description</label><input type="text" value={form.description} onChange={e => setForm(p => ({ ...p, description: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
                    </div>
                </div>
                <div className="flex justify-end gap-3">
                    <Button type="button" onClick={onBack} className="bg-gray-100 text-gray-700 hover:bg-gray-200">Cancel</Button>
                    <Button type="submit" disabled={saving}>{saving ? 'Saving...' : 'Save'}</Button>
                </div>
            </form>
        </div>
    );
}
