import { useState } from 'react';
import { Plus, Search, Eye, Edit2, Trash2, ArrowLeft, Settings } from 'lucide-react';
import { workCentersApi } from '../../api/endpoints';
import { useCrudApi } from '../../hooks/useCrudApi';
import { Button } from '../../components/ui/Button';
import { Spinner } from '../../components/ui/Loader';

export default function WorkCentersPage() {
    const {
        data: centers, loading, saving, error, setError,
        currentPage, lastPage, total,
        search, fetchData, handlePageChange, handleSearchChange,
        store, update, destroy,
    } = useCrudApi(workCentersApi);

    const [view, setView] = useState('list');
    const [selectedId, setSelectedId] = useState(null);
    const [detail, setDetail] = useState(null);

    const loadDetail = async (id) => {
        try {
            const res = await workCentersApi.show(id);
            setDetail(res.data.data);
            setSelectedId(id);
            setView('detail');
        } catch {}
    };

    const handleDelete = async (id) => {
        if (window.confirm('Delete this work center?')) {
            const ok = await destroy(id);
            if (ok && view === 'detail') setView('list');
        }
    };

    if (view === 'create' || view === 'edit') {
        return <WorkCenterForm isEdit={view === 'edit'} itemId={selectedId}
            onBack={() => { setView(selectedId ? 'detail' : 'list'); setError(null); }}
            onSave={async (data) => {
                let ok = view === 'edit' ? await update(selectedId, data) : await store(data);
                if (ok) { view === 'edit' ? loadDetail(selectedId) : setView('list'); }
                return ok;
            }}
            saving={saving} error={error} />;
    }

    if (view === 'detail' && detail) {
        return (
            <div className="space-y-6">
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div className="flex items-center gap-3">
                        <button onClick={() => { setView('list'); setDetail(null); }} className="p-2 rounded-lg hover:bg-gray-100"><ArrowLeft className="h-5 w-5" /></button>
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">{detail.name}</h1>
                            <p className="text-sm text-gray-500">{detail.code}</p>
                        </div>
                    </div>
                    <div className="flex gap-2">
                        <Button onClick={() => { setSelectedId(detail.id); setView('edit'); }} className="flex items-center gap-2 bg-blue-50 text-blue-700 hover:bg-blue-100"><Edit2 className="h-4 w-4" /> Edit</Button>
                        <Button onClick={() => handleDelete(detail.id)} className="flex items-center gap-2 bg-red-50 text-red-700 hover:bg-red-100"><Trash2 className="h-4 w-4" /> Delete</Button>
                    </div>
                </div>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <InfoCard label="Code" value={detail.code} />
                    <InfoCard label="Capacity/Hour" value={detail.capacity_per_hour} />
                    <InfoCard label="Cost/Hour" value={detail.cost_per_hour} />
                    <InfoCard label="Efficiency" value={`${detail.efficiency}%`} />
                    <InfoCard label="Active" value={detail.is_active ? 'Yes' : 'No'} />
                    <InfoCard label="Description" value={detail.description || '-'} />
                </div>
            </div>
        );
    }

    return (
        <div className="space-y-6">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Work Centers</h1>
                    <p className="text-sm text-gray-500">{total} work centers total</p>
                </div>
                <Button onClick={() => { setSelectedId(null); setView('create'); }} className="flex items-center gap-2"><Plus className="h-4 w-4" /> New Work Center</Button>
            </div>
            <div className="relative max-w-md">
                <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                <input type="text" value={search} onChange={(e) => handleSearchChange(e.target.value)} placeholder="Search..." className="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" />
            </div>
            {loading ? <div className="flex justify-center py-20"><Spinner /></div> : (
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Code</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Name</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">Capacity/Hr</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">Cost/Hr</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">Efficiency</th>
                                    <th className="px-4 py-3 text-center font-medium text-gray-600">Active</th>
                                    <th className="px-4 py-3 text-center font-medium text-gray-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {centers.map((wc) => (
                                    <tr key={wc.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3"><button onClick={() => loadDetail(wc.id)} className="font-medium text-indigo-600 hover:text-indigo-800">{wc.code}</button></td>
                                        <td className="px-4 py-3 text-gray-800">{wc.name}</td>
                                        <td className="px-4 py-3 text-right">{wc.capacity_per_hour}</td>
                                        <td className="px-4 py-3 text-right">{wc.cost_per_hour}</td>
                                        <td className="px-4 py-3 text-right">{wc.efficiency}%</td>
                                        <td className="px-4 py-3 text-center">{wc.is_active ? <span className="text-green-600">✓</span> : <span className="text-red-500">✗</span>}</td>
                                        <td className="px-4 py-3"><div className="flex items-center justify-center gap-1">
                                            <button onClick={() => loadDetail(wc.id)} className="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100"><Eye className="h-4 w-4" /></button>
                                            <button onClick={() => { setSelectedId(wc.id); setView('edit'); }} className="p-1.5 rounded-lg text-blue-500 hover:bg-blue-50"><Edit2 className="h-4 w-4" /></button>
                                            <button onClick={() => handleDelete(wc.id)} className="p-1.5 rounded-lg text-red-500 hover:bg-red-50"><Trash2 className="h-4 w-4" /></button>
                                        </div></td>
                                    </tr>
                                ))}
                                {centers.length === 0 && <tr><td colSpan={7} className="px-4 py-12 text-center text-gray-400">No work centers found.</td></tr>}
                            </tbody>
                        </table>
                    </div>
                </div>
            )}
        </div>
    );
}

function InfoCard({ label, value }) {
    return <div className="bg-white rounded-xl border border-gray-200 p-4"><p className="text-xs text-gray-500 uppercase tracking-wide mb-1">{label}</p><p className="text-sm font-medium text-gray-900">{value}</p></div>;
}

function WorkCenterForm({ isEdit, itemId, onBack, onSave, saving, error }) {
    const [form, setForm] = useState({ name: '', code: '', description: '', capacity_per_hour: 1, cost_per_hour: 0, efficiency: 100, is_active: true });
    const [loadingForm, setLoadingForm] = useState(isEdit);

    useState(() => {
        if (isEdit && itemId) {
            workCentersApi.show(itemId).then(res => {
                const d = res.data.data;
                setForm({ name: d.name, code: d.code, description: d.description || '', capacity_per_hour: d.capacity_per_hour, cost_per_hour: d.cost_per_hour, efficiency: d.efficiency, is_active: d.is_active });
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
                <h1 className="text-2xl font-bold text-gray-900">{isEdit ? 'Edit Work Center' : 'New Work Center'}</h1>
            </div>
            {error && <div className="rounded-lg bg-red-50 p-4 text-red-700 text-sm"><p className="font-medium">{error.message}</p>{errorMessages.length > 0 && <ul className="mt-1 list-disc list-inside">{errorMessages.map((m, i) => <li key={i}>{m}</li>)}</ul>}</div>}
            <form onSubmit={async (e) => { e.preventDefault(); await onSave(form); }} className="space-y-6">
                <div className="bg-white rounded-xl border border-gray-200 p-6">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Name *</label><input type="text" value={form.name} onChange={e => setForm(p => ({ ...p, name: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required /></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Code</label><input type="text" value={form.code} onChange={e => setForm(p => ({ ...p, code: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
                        <div className="md:col-span-2"><label className="block text-sm font-medium text-gray-700 mb-1">Description</label><input type="text" value={form.description} onChange={e => setForm(p => ({ ...p, description: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Capacity/Hour *</label><input type="number" min="1" value={form.capacity_per_hour} onChange={e => setForm(p => ({ ...p, capacity_per_hour: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required /></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Cost/Hour</label><input type="number" min="0" step="0.01" value={form.cost_per_hour} onChange={e => setForm(p => ({ ...p, cost_per_hour: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Efficiency %</label><input type="number" min="0" max="100" value={form.efficiency} onChange={e => setForm(p => ({ ...p, efficiency: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
                        <div className="flex items-center gap-2 pt-6"><input type="checkbox" checked={form.is_active} onChange={e => setForm(p => ({ ...p, is_active: e.target.checked }))} className="rounded" /><label className="text-sm text-gray-700">Active</label></div>
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
