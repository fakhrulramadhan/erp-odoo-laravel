import { useState } from 'react';
import { Plus, Search, Eye, Edit2, Trash2, ArrowLeft, Settings } from 'lucide-react';
import { equipmentApi, workCentersApi } from '../../api/endpoints';
import { useCrudApi } from '../../hooks/useCrudApi';
import { StatusBadge, formatDate } from '../../components/ui/StatusBadge';
import { Button } from '../../components/ui/Button';
import { Spinner } from '../../components/ui/Loader';

export default function EquipmentPage() {
    const {
        data: equipment, loading, saving, error, setError,
        currentPage, lastPage, total,
        search, fetchData, handlePageChange, handleSearchChange,
        store, update, destroy,
    } = useCrudApi(equipmentApi);

    const [view, setView] = useState('list');
    const [selectedId, setSelectedId] = useState(null);
    const [detail, setDetail] = useState(null);
    const [workCenters, setWorkCenters] = useState([]);

    useState(() => {
        workCentersApi.list({ per_page: 999 }).then(res => {
            const d = res.data?.data;
            setWorkCenters(Array.isArray(d) ? d : (d?.data || []));
        }).catch(() => {});
    });

    const loadDetail = async (id) => {
        try { const res = await equipmentApi.show(id); setDetail(res.data.data); setSelectedId(id); setView('detail'); } catch {}
    };

    const handleDelete = async (id) => {
        if (window.confirm('Delete this equipment?')) { const ok = await destroy(id); if (ok && view === 'detail') setView('list'); }
    };

    if (view === 'create' || view === 'edit') {
        return <EquipmentForm isEdit={view === 'edit'} itemId={selectedId} workCenters={workCenters}
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
                    <InfoCard label="Work Center" value={detail.work_center?.name || '-'} />
                    <InfoCard label="Serial Number" value={detail.serial_number || '-'} />
                    <InfoCard label="Manufacturer" value={detail.manufacturer || '-'} />
                    <InfoCard label="Model" value={detail.model || '-'} />
                    <InfoCard label="Purchase Date" value={formatDate(detail.purchase_date)} />
                    <InfoCard label="Warranty Expiry" value={formatDate(detail.warranty_expiry)} />
                    <InfoCard label="Maintenance Interval" value={detail.maintenance_interval_days ? `${detail.maintenance_interval_days} days` : '-'} />
                    <InfoCard label="Status" value={detail.status || '-'} />
                </div>
                {detail.description && <div className="bg-white rounded-xl border border-gray-200 p-6"><h3 className="font-semibold text-gray-900 mb-2">Description</h3><p className="text-gray-600 text-sm">{detail.description}</p></div>}
            </div>
        );
    }

    return (
        <div className="space-y-6">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div><h1 className="text-2xl font-bold text-gray-900">Equipment</h1><p className="text-sm text-gray-500">{total} equipment total</p></div>
                <Button onClick={() => { setSelectedId(null); setView('create'); }} className="flex items-center gap-2"><Plus className="h-4 w-4" /> New Equipment</Button>
            </div>
            <div className="relative max-w-md"><Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" /><input type="text" value={search} onChange={(e) => handleSearchChange(e.target.value)} placeholder="Search..." className="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" /></div>
            {loading ? <div className="flex justify-center py-20"><Spinner /></div> : (
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 border-b border-gray-200"><tr>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Code</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Name</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Work Center</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Serial</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                                <th className="px-4 py-3 text-center font-medium text-gray-600">Actions</th>
                            </tr></thead>
                            <tbody className="divide-y divide-gray-100">
                                {equipment.map((eq) => (
                                    <tr key={eq.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3"><button onClick={() => loadDetail(eq.id)} className="font-medium text-indigo-600 hover:text-indigo-800">{eq.code}</button></td>
                                        <td className="px-4 py-3 text-gray-800">{eq.name}</td>
                                        <td className="px-4 py-3 text-gray-600">{eq.work_center?.name || '-'}</td>
                                        <td className="px-4 py-3 text-gray-600">{eq.serial_number || '-'}</td>
                                        <td className="px-4 py-3"><span className="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-green-100 text-green-700 capitalize">{eq.status || 'active'}</span></td>
                                        <td className="px-4 py-3"><div className="flex items-center justify-center gap-1">
                                            <button onClick={() => loadDetail(eq.id)} className="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100"><Eye className="h-4 w-4" /></button>
                                            <button onClick={() => { setSelectedId(eq.id); setView('edit'); }} className="p-1.5 rounded-lg text-blue-500 hover:bg-blue-50"><Edit2 className="h-4 w-4" /></button>
                                            <button onClick={() => handleDelete(eq.id)} className="p-1.5 rounded-lg text-red-500 hover:bg-red-50"><Trash2 className="h-4 w-4" /></button>
                                        </div></td>
                                    </tr>
                                ))}
                                {equipment.length === 0 && <tr><td colSpan={6} className="px-4 py-12 text-center text-gray-400">No equipment found.</td></tr>}
                            </tbody>
                        </table>
                    </div>
                </div>
            )}
        </div>
    );
}

function InfoCard({ label, value }) { return <div className="bg-white rounded-xl border border-gray-200 p-4"><p className="text-xs text-gray-500 uppercase tracking-wide mb-1">{label}</p><p className="text-sm font-medium text-gray-900">{value}</p></div>; }

function EquipmentForm({ isEdit, itemId, workCenters, onBack, onSave, saving, error }) {
    const [form, setForm] = useState({ name: '', code: '', description: '', work_center_id: '', serial_number: '', manufacturer: '', model: '', purchase_date: '', warranty_expiry: '', maintenance_interval_days: 90, status: 'active' });
    const [loadingForm, setLoadingForm] = useState(isEdit);

    useState(() => {
        if (isEdit && itemId) {
            equipmentApi.show(itemId).then(res => {
                const d = res.data.data;
                setForm({ name: d.name, code: d.code, description: d.description || '', work_center_id: d.work_center_id || '', serial_number: d.serial_number || '', manufacturer: d.manufacturer || '', model: d.model || '', purchase_date: d.purchase_date || '', warranty_expiry: d.warranty_expiry || '', maintenance_interval_days: d.maintenance_interval_days || 90, status: d.status || 'active' });
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
                <h1 className="text-2xl font-bold text-gray-900">{isEdit ? 'Edit Equipment' : 'New Equipment'}</h1>
            </div>
            {error && <div className="rounded-lg bg-red-50 p-4 text-red-700 text-sm"><p className="font-medium">{error.message}</p>{errorMessages.length > 0 && <ul className="mt-1 list-disc list-inside">{errorMessages.map((m, i) => <li key={i}>{m}</li>)}</ul>}</div>}
            <form onSubmit={async (e) => { e.preventDefault(); await onSave(form); }} className="space-y-6">
                <div className="bg-white rounded-xl border border-gray-200 p-6">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Name *</label><input type="text" value={form.name} onChange={e => setForm(p => ({ ...p, name: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required /></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Code</label><input type="text" value={form.code} onChange={e => setForm(p => ({ ...p, code: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Work Center</label><select value={form.work_center_id} onChange={e => setForm(p => ({ ...p, work_center_id: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><option value="">Select</option>{workCenters.map(wc => <option key={wc.id} value={wc.id}>{wc.name}</option>)}</select></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Serial Number</label><input type="text" value={form.serial_number} onChange={e => setForm(p => ({ ...p, serial_number: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Manufacturer</label><input type="text" value={form.manufacturer} onChange={e => setForm(p => ({ ...p, manufacturer: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Model</label><input type="text" value={form.model} onChange={e => setForm(p => ({ ...p, model: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Purchase Date</label><input type="date" value={form.purchase_date} onChange={e => setForm(p => ({ ...p, purchase_date: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Warranty Expiry</label><input type="date" value={form.warranty_expiry} onChange={e => setForm(p => ({ ...p, warranty_expiry: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Maintenance Interval (days)</label><input type="number" min="1" value={form.maintenance_interval_days} onChange={e => setForm(p => ({ ...p, maintenance_interval_days: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
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
