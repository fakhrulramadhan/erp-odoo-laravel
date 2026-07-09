import { useState, useEffect } from 'react';
import { Plus, Search, Eye, Edit2, Trash2, ArrowLeft, ArrowRightLeft } from 'lucide-react';
import { routingsApi, workCentersApi } from '../../api/endpoints';
import { useCrudApi } from '../../hooks/useCrudApi';
import { formatDate } from '../../components/ui/StatusBadge';
import { Button } from '../../components/ui/Button';
import { Spinner } from '../../components/ui/Loader';

export default function RoutingsPage() {
    const {
        data: routings, loading, saving, error, setError,
        currentPage, lastPage, total,
        search, fetchData, handlePageChange, handleSearchChange,
        store, update, destroy,
    } = useCrudApi(routingsApi);

    const [view, setView] = useState('list');
    const [selectedId, setSelectedId] = useState(null);
    const [detail, setDetail] = useState(null);
    const [workCenters, setWorkCenters] = useState([]);

    useEffect(() => {
        workCentersApi.list({ per_page: 999 }).then(res => {
            const d = res.data?.data;
            setWorkCenters(Array.isArray(d) ? d : (d?.data || []));
        }).catch(() => {});
    }, []);

    const loadDetail = async (id) => {
        try { const res = await routingsApi.show(id); setDetail(res.data.data); setSelectedId(id); setView('detail'); } catch {}
    };

    const handleDelete = async (id) => {
        if (window.confirm('Delete this routing?')) { const ok = await destroy(id); if (ok && view === 'detail') setView('list'); }
    };

    if (view === 'create' || view === 'edit') {
        return <RoutingForm isEdit={view === 'edit'} itemId={selectedId} workCenters={workCenters}
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
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">{detail.name}</h1>
                            <p className="text-sm text-gray-500">{detail.routing_number}</p>
                        </div>
                    </div>
                    <div className="flex gap-2">
                        <Button onClick={() => { setSelectedId(detail.id); setView('edit'); }} className="flex items-center gap-2 bg-blue-50 text-blue-700 hover:bg-blue-100"><Edit2 className="h-4 w-4" /> Edit</Button>
                        <Button onClick={() => handleDelete(detail.id)} className="flex items-center gap-2 bg-red-50 text-red-700 hover:bg-red-100"><Trash2 className="h-4 w-4" /> Delete</Button>
                    </div>
                </div>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <InfoCard label="Routing Number" value={detail.routing_number} />
                    <InfoCard label="Name" value={detail.name} />
                    <InfoCard label="Active" value={detail.is_active ? 'Yes' : 'No'} />
                    <InfoCard label="Description" value={detail.description || '-'} />
                </div>
                {detail.operations && detail.operations.length > 0 && (
                    <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <div className="px-6 py-4 border-b border-gray-200"><h3 className="font-semibold text-gray-900">Operations</h3></div>
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead className="bg-gray-50"><tr>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Seq</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Name</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Work Center</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">Duration (min)</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">Setup (min)</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">Cost/Hr</th>
                                </tr></thead>
                                <tbody className="divide-y divide-gray-100">
                                    {detail.operations.map((op) => (
                                        <tr key={op.id} className="hover:bg-gray-50">
                                            <td className="px-4 py-3">{op.sequence}</td>
                                            <td className="px-4 py-3 font-medium">{op.name}</td>
                                            <td className="px-4 py-3 text-gray-600">{op.work_center?.name || '-'}</td>
                                            <td className="px-4 py-3 text-right">{op.duration_minutes}</td>
                                            <td className="px-4 py-3 text-right">{op.setup_time_minutes || 0}</td>
                                            <td className="px-4 py-3 text-right">{op.cost_per_hour || 0}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}
            </div>
        );
    }

    return (
        <div className="space-y-6">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div><h1 className="text-2xl font-bold text-gray-900">Routings</h1><p className="text-sm text-gray-500">{total} routings total</p></div>
                <Button onClick={() => { setSelectedId(null); setView('create'); }} className="flex items-center gap-2"><Plus className="h-4 w-4" /> New Routing</Button>
            </div>
            <div className="relative max-w-md"><Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" /><input type="text" value={search} onChange={(e) => handleSearchChange(e.target.value)} placeholder="Search..." className="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" /></div>
            {loading ? <div className="flex justify-center py-20"><Spinner /></div> : (
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 border-b border-gray-200"><tr>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Routing Number</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Name</th>
                                <th className="px-4 py-3 text-center font-medium text-gray-600">Active</th>
                                <th className="px-4 py-3 text-center font-medium text-gray-600">Actions</th>
                            </tr></thead>
                            <tbody className="divide-y divide-gray-100">
                                {routings.map((r) => (
                                    <tr key={r.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3"><button onClick={() => loadDetail(r.id)} className="font-medium text-indigo-600 hover:text-indigo-800">{r.routing_number}</button></td>
                                        <td className="px-4 py-3 text-gray-800">{r.name}</td>
                                        <td className="px-4 py-3 text-center">{r.is_active ? <span className="text-green-600">✓</span> : <span className="text-red-500">✗</span>}</td>
                                        <td className="px-4 py-3"><div className="flex items-center justify-center gap-1">
                                            <button onClick={() => loadDetail(r.id)} className="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100"><Eye className="h-4 w-4" /></button>
                                            <button onClick={() => { setSelectedId(r.id); setView('edit'); }} className="p-1.5 rounded-lg text-blue-500 hover:bg-blue-50"><Edit2 className="h-4 w-4" /></button>
                                            <button onClick={() => handleDelete(r.id)} className="p-1.5 rounded-lg text-red-500 hover:bg-red-50"><Trash2 className="h-4 w-4" /></button>
                                        </div></td>
                                    </tr>
                                ))}
                                {routings.length === 0 && <tr><td colSpan={4} className="px-4 py-12 text-center text-gray-400">No routings found.</td></tr>}
                            </tbody>
                        </table>
                    </div>
                </div>
            )}
        </div>
    );
}

function InfoCard({ label, value }) { return <div className="bg-white rounded-xl border border-gray-200 p-4"><p className="text-xs text-gray-500 uppercase tracking-wide mb-1">{label}</p><p className="text-sm font-medium text-gray-900">{value}</p></div>; }

function RoutingForm({ isEdit, itemId, workCenters, onBack, onSave, saving, error }) {
    const [form, setForm] = useState({
        name: '', description: '', is_active: true,
        operations: [{ sequence: 1, name: '', work_center_id: '', duration_minutes: 0, setup_time_minutes: 0, cost_per_hour: 0 }],
    });
    const [loadingForm, setLoadingForm] = useState(isEdit);

    useEffect(() => {
        if (isEdit && itemId) {
            routingsApi.show(itemId).then(res => {
                const d = res.data.data;
                setForm({
                    name: d.name || '', description: d.description || '', is_active: d.is_active,
                    operations: (d.operations || []).map(op => ({ sequence: op.sequence, name: op.name, work_center_id: op.work_center_id || '', duration_minutes: op.duration_minutes, setup_time_minutes: op.setup_time_minutes || 0, cost_per_hour: op.cost_per_hour || 0 })),
                });
                setLoadingForm(false);
            }).catch(() => setLoadingForm(false));
        }
    }, [isEdit, itemId]);

    const updateOp = (i, f, v) => setForm(p => { const ops = [...p.operations]; ops[i] = { ...ops[i], [f]: v }; return { ...p, operations: ops }; });

    if (loadingForm) return <div className="flex justify-center py-20"><Spinner /></div>;
    const errorMessages = error?.errors ? Object.values(error.errors).flat() : [];

    return (
        <div className="space-y-6">
            <div className="flex items-center gap-3">
                <button onClick={onBack} className="p-2 rounded-lg hover:bg-gray-100"><ArrowLeft className="h-5 w-5" /></button>
                <h1 className="text-2xl font-bold text-gray-900">{isEdit ? 'Edit Routing' : 'New Routing'}</h1>
            </div>
            {error && <div className="rounded-lg bg-red-50 p-4 text-red-700 text-sm"><p className="font-medium">{error.message}</p>{errorMessages.length > 0 && <ul className="mt-1 list-disc list-inside">{errorMessages.map((m, i) => <li key={i}>{m}</li>)}</ul>}</div>}
            <form onSubmit={async (e) => { e.preventDefault(); await onSave(form); }} className="space-y-6">
                <div className="bg-white rounded-xl border border-gray-200 p-6">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Name *</label><input type="text" value={form.name} onChange={e => setForm(p => ({ ...p, name: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required /></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Description</label><input type="text" value={form.description} onChange={e => setForm(p => ({ ...p, description: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
                        <div className="flex items-center gap-2"><input type="checkbox" checked={form.is_active} onChange={e => setForm(p => ({ ...p, is_active: e.target.checked }))} className="rounded" /><label className="text-sm text-gray-700">Active</label></div>
                    </div>
                </div>
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 className="font-semibold text-gray-900">Operations</h3>
                        <Button type="button" onClick={() => setForm(p => ({ ...p, operations: [...p.operations, { sequence: p.operations.length + 1, name: '', work_center_id: '', duration_minutes: 0, setup_time_minutes: 0, cost_per_hour: 0 }] }))} className="flex items-center gap-2 text-sm bg-indigo-50 text-indigo-700 hover:bg-indigo-100"><Plus className="h-4 w-4" /> Add</Button>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50"><tr>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Seq</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Name *</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Work Center</th>
                                <th className="px-4 py-3 text-right font-medium text-gray-600">Duration</th>
                                <th className="px-4 py-3 text-right font-medium text-gray-600">Setup</th>
                                <th className="px-4 py-3 w-12"></th>
                            </tr></thead>
                            <tbody className="divide-y divide-gray-100">
                                {form.operations.map((op, i) => (
                                    <tr key={i}>
                                        <td className="px-4 py-2"><input type="number" min="1" value={op.sequence} onChange={e => updateOp(i, 'sequence', e.target.value)} className="w-16 rounded border border-gray-300 px-2 py-1.5 text-sm" /></td>
                                        <td className="px-4 py-2"><input type="text" value={op.name} onChange={e => updateOp(i, 'name', e.target.value)} className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" required /></td>
                                        <td className="px-4 py-2"><select value={op.work_center_id} onChange={e => updateOp(i, 'work_center_id', e.target.value)} className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm"><option value="">Select</option>{workCenters.map(wc => <option key={wc.id} value={wc.id}>{wc.name}</option>)}</select></td>
                                        <td className="px-4 py-2"><input type="number" min="0" value={op.duration_minutes} onChange={e => updateOp(i, 'duration_minutes', e.target.value)} className="w-20 rounded border border-gray-300 px-2 py-1.5 text-sm text-right" /></td>
                                        <td className="px-4 py-2"><input type="number" min="0" value={op.setup_time_minutes} onChange={e => updateOp(i, 'setup_time_minutes', e.target.value)} className="w-20 rounded border border-gray-300 px-2 py-1.5 text-sm text-right" /></td>
                                        <td className="px-4 py-2"><button type="button" onClick={() => { if (form.operations.length > 1) setForm(p => ({ ...p, operations: p.operations.filter((_, j) => j !== i) })); }} className="p-1 rounded text-red-500 hover:bg-red-50"><Trash2 className="h-4 w-4" /></button></td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
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
