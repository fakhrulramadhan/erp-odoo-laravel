import { useState, useEffect } from 'react';
import {
    Plus, Search, Eye, Edit2, Trash2, ArrowLeft,
    Wrench, Play, CheckCircle, XCircle, Calendar
} from 'lucide-react';
import { maintenanceOrdersApi, equipmentApi } from '../../api/endpoints';
import { useCrudApi } from '../../hooks/useCrudApi';
import { StatusBadge, formatDate } from '../../components/ui/StatusBadge';
import { Button } from '../../components/ui/Button';
import { Modal } from '../../components/ui/Modal';
import { Spinner } from '../../components/ui/Loader';

const MN_STATUS_OPTIONS = [
    { value: '', label: 'All Status' },
    { value: 'draft', label: 'Draft' },
    { value: 'requested', label: 'Requested' },
    { value: 'scheduled', label: 'Scheduled' },
    { value: 'in_progress', label: 'In Progress' },
    { value: 'completed', label: 'Completed' },
    { value: 'cancelled', label: 'Cancelled' },
];

export default function MaintenanceOrdersPage() {
    const {
        data: orders, loading, saving, error, setError,
        currentPage, lastPage, total,
        search, filterValues,
        fetchData, handlePageChange, handleSearchChange, handleFilterChange,
        store, update, destroy, performAction,
    } = useCrudApi(maintenanceOrdersApi);

    const [view, setView] = useState('list');
    const [selectedId, setSelectedId] = useState(null);
    const [detail, setDetail] = useState(null);
    const [confirmAction, setConfirmAction] = useState(null);
    const [equipment, setEquipment] = useState([]);

    useEffect(() => {
        equipmentApi.list({ per_page: 999 }).then(res => {
            const d = res.data?.data;
            setEquipment(Array.isArray(d) ? d : (d?.data || []));
        }).catch(() => {});
    }, []);

    const loadDetail = async (id) => {
        try { const res = await maintenanceOrdersApi.show(id); setDetail(res.data.data); setSelectedId(id); setView('detail'); } catch {}
    };

    const handleAction = (action, id) => setConfirmAction({ action, id });
    const executeAction = async () => {
        if (!confirmAction) return;
        const { action, id } = confirmAction;
        const map = { schedule: maintenanceOrdersApi.schedule, startWork: maintenanceOrdersApi.startWork, complete: maintenanceOrdersApi.complete, cancel: maintenanceOrdersApi.cancel };
        const result = await performAction(map[action], id);
        if (result && view === 'detail' && id === selectedId) loadDetail(id);
        setConfirmAction(null);
    };

    const handleDelete = async (id) => {
        if (window.confirm('Delete this maintenance order?')) { const ok = await destroy(id); if (ok && view === 'detail') setView('list'); }
    };

    const statusFilter = filterValues.status || '';

    if (view === 'create' || view === 'edit') {
        return <MaintenanceOrderForm isEdit={view === 'edit'} itemId={selectedId} equipment={equipment}
            onBack={() => { setView(selectedId ? 'detail' : 'list'); setError(null); }}
            onSave={async (data) => { let ok = view === 'edit' ? await update(selectedId, data) : await store(data); if (ok) { view === 'edit' ? loadDetail(selectedId) : setView('list'); } return ok; }}
            saving={saving} error={error} />;
    }

    if (view === 'detail' && detail) {
        const s = detail.status;
        return (
            <div className="space-y-6">
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div className="flex items-center gap-3">
                        <button onClick={() => { setView('list'); setDetail(null); }} className="p-2 rounded-lg hover:bg-gray-100"><ArrowLeft className="h-5 w-5" /></button>
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">{detail.maintenance_number}</h1>
                            <StatusBadge status={detail.status} label={detail.status_label} />
                        </div>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        {['draft', 'requested'].includes(s) && <Button onClick={() => { setSelectedId(detail.id); setView('edit'); }} className="flex items-center gap-2 bg-blue-50 text-blue-700 hover:bg-blue-100"><Edit2 className="h-4 w-4" /> Edit</Button>}
                        {['draft', 'requested'].includes(s) && <Button onClick={() => handleAction('schedule', detail.id)} className="flex items-center gap-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100"><Calendar className="h-4 w-4" /> Schedule</Button>}
                        {s === 'scheduled' && <Button onClick={() => handleAction('startWork', detail.id)} className="flex items-center gap-2 bg-blue-50 text-blue-700 hover:bg-blue-100"><Play className="h-4 w-4" /> Start Work</Button>}
                        {s === 'in_progress' && <Button onClick={() => handleAction('complete', detail.id)} className="flex items-center gap-2 bg-green-50 text-green-700 hover:bg-green-100"><CheckCircle className="h-4 w-4" /> Complete</Button>}
                        {['draft', 'requested', 'scheduled'].includes(s) && <Button onClick={() => handleAction('cancel', detail.id)} className="flex items-center gap-2 bg-red-50 text-red-700 hover:bg-red-100"><XCircle className="h-4 w-4" /> Cancel</Button>}
                        {['draft', 'requested'].includes(s) && <Button onClick={() => handleDelete(detail.id)} className="flex items-center gap-2 bg-red-50 text-red-700 hover:bg-red-100"><Trash2 className="h-4 w-4" /> Delete</Button>}
                    </div>
                </div>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <InfoCard label="Equipment" value={detail.equipment?.name || '-'} />
                    <InfoCard label="Maintenance Type" value={detail.maintenance_type || '-'} />
                    <InfoCard label="Priority" value={detail.priority || '-'} />
                    <InfoCard label="Scheduled Date" value={formatDate(detail.scheduled_date)} />
                    <InfoCard label="Completed Date" value={formatDate(detail.completed_date)} />
                    <InfoCard label="Estimated Cost" value={detail.estimated_cost || '-'} />
                    <InfoCard label="Actual Cost" value={detail.actual_cost || '-'} />
                    <InfoCard label="Assigned To" value={detail.assigned_to?.name || '-'} />
                </div>
                {detail.description && <div className="bg-white rounded-xl border border-gray-200 p-6"><h3 className="font-semibold text-gray-900 mb-2">Description</h3><p className="text-gray-600 text-sm">{detail.description}</p></div>}
                {detail.notes && <div className="bg-white rounded-xl border border-gray-200 p-6"><h3 className="font-semibold text-gray-900 mb-2">Notes</h3><p className="text-gray-600 text-sm">{detail.notes}</p></div>}
            </div>
        );
    }

    return (
        <div className="space-y-6">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div><h1 className="text-2xl font-bold text-gray-900">Maintenance Orders</h1><p className="text-sm text-gray-500">{total} orders total</p></div>
                <Button onClick={() => { setSelectedId(null); setView('create'); }} className="flex items-center gap-2"><Plus className="h-4 w-4" /> New Maintenance Order</Button>
            </div>
            <div className="flex flex-col sm:flex-row gap-3">
                <div className="relative flex-1"><Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" /><input type="text" value={search} onChange={(e) => handleSearchChange(e.target.value)} placeholder="Search..." className="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" /></div>
                <select value={statusFilter} onChange={(e) => handleFilterChange({ ...filterValues, status: e.target.value })} className="rounded-lg border border-gray-300 px-3 py-2 text-sm">{MN_STATUS_OPTIONS.map(o => <option key={o.value} value={o.value}>{o.label}</option>)}</select>
            </div>
            {loading ? <div className="flex justify-center py-20"><Spinner /></div> : (
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 border-b border-gray-200"><tr>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Number</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Equipment</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Type</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Scheduled</th>
                                <th className="px-4 py-3 text-center font-medium text-gray-600">Actions</th>
                            </tr></thead>
                            <tbody className="divide-y divide-gray-100">
                                {orders.map((mo) => (
                                    <tr key={mo.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3"><button onClick={() => loadDetail(mo.id)} className="font-medium text-indigo-600 hover:text-indigo-800">{mo.maintenance_number}</button></td>
                                        <td className="px-4 py-3 text-gray-800">{mo.equipment?.name || '-'}</td>
                                        <td className="px-4 py-3 text-gray-600 capitalize">{mo.maintenance_type || '-'}</td>
                                        <td className="px-4 py-3"><StatusBadge status={mo.status} label={mo.status_label} /></td>
                                        <td className="px-4 py-3 text-gray-600">{formatDate(mo.scheduled_date)}</td>
                                        <td className="px-4 py-3"><div className="flex items-center justify-center gap-1">
                                            <button onClick={() => loadDetail(mo.id)} className="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100"><Eye className="h-4 w-4" /></button>
                                        </div></td>
                                    </tr>
                                ))}
                                {orders.length === 0 && <tr><td colSpan={6} className="px-4 py-12 text-center text-gray-400">No maintenance orders found.</td></tr>}
                            </tbody>
                        </table>
                    </div>
                </div>
            )}
            {confirmAction && (
                <Modal onClose={() => setConfirmAction(null)} title={`Confirm ${confirmAction.action}`}>
                    <p className="text-gray-600 mb-4">Are you sure you want to <strong>{confirmAction.action}</strong>?</p>
                    <div className="flex justify-end gap-3">
                        <Button onClick={() => setConfirmAction(null)} className="bg-gray-100 text-gray-700 hover:bg-gray-200">Cancel</Button>
                        <Button onClick={executeAction} disabled={saving}>{saving ? 'Processing...' : 'Confirm'}</Button>
                    </div>
                </Modal>
            )}
        </div>
    );
}

function InfoCard({ label, value }) { return <div className="bg-white rounded-xl border border-gray-200 p-4"><p className="text-xs text-gray-500 uppercase tracking-wide mb-1">{label}</p><p className="text-sm font-medium text-gray-900">{value}</p></div>; }

function MaintenanceOrderForm({ isEdit, itemId, equipment, onBack, onSave, saving, error }) {
    const [form, setForm] = useState({ equipment_id: '', maintenance_type: 'preventive', priority: 'medium', scheduled_date: '', estimated_cost: 0, description: '', notes: '' });
    const [loadingForm, setLoadingForm] = useState(isEdit);

    useState(() => {
        if (isEdit && itemId) {
            maintenanceOrdersApi.show(itemId).then(res => {
                const d = res.data.data;
                setForm({ equipment_id: d.equipment_id || '', maintenance_type: d.maintenance_type || 'preventive', priority: d.priority || 'medium', scheduled_date: d.scheduled_date || '', estimated_cost: d.estimated_cost || 0, description: d.description || '', notes: d.notes || '' });
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
                <h1 className="text-2xl font-bold text-gray-900">{isEdit ? 'Edit Maintenance Order' : 'New Maintenance Order'}</h1>
            </div>
            {error && <div className="rounded-lg bg-red-50 p-4 text-red-700 text-sm"><p className="font-medium">{error.message}</p>{errorMessages.length > 0 && <ul className="mt-1 list-disc list-inside">{errorMessages.map((m, i) => <li key={i}>{m}</li>)}</ul>}</div>}
            <form onSubmit={async (e) => { e.preventDefault(); await onSave(form); }} className="space-y-6">
                <div className="bg-white rounded-xl border border-gray-200 p-6">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Equipment *</label><select value={form.equipment_id} onChange={e => setForm(p => ({ ...p, equipment_id: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required><option value="">Select Equipment</option>{equipment.map(eq => <option key={eq.id} value={eq.id}>{eq.name}</option>)}</select></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Type *</label><select value={form.maintenance_type} onChange={e => setForm(p => ({ ...p, maintenance_type: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><option value="preventive">Preventive</option><option value="corrective">Corrective</option><option value="predictive">Predictive</option><option value="emergency">Emergency</option></select></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Priority</label><select value={form.priority} onChange={e => setForm(p => ({ ...p, priority: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option><option value="critical">Critical</option></select></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Scheduled Date</label><input type="date" value={form.scheduled_date} onChange={e => setForm(p => ({ ...p, scheduled_date: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Estimated Cost</label><input type="number" min="0" step="0.01" value={form.estimated_cost} onChange={e => setForm(p => ({ ...p, estimated_cost: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
                        <div className="md:col-span-2"><label className="block text-sm font-medium text-gray-700 mb-1">Description</label><textarea value={form.description} onChange={e => setForm(p => ({ ...p, description: e.target.value }))} rows={3} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
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
