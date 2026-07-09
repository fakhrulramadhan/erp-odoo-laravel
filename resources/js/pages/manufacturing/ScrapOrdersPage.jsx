import { useState, useEffect } from 'react';
import {
    Plus, Search, Eye, Edit2, Trash2, ArrowLeft,
    AlertTriangle, CheckCircle, Play
} from 'lucide-react';
import { scrapOrdersApi, manufacturingOrdersApi, masterDataApi } from '../../api/endpoints';
import { useCrudApi } from '../../hooks/useCrudApi';
import { StatusBadge, formatCurrency, formatDate } from '../../components/ui/StatusBadge';
import { Button } from '../../components/ui/Button';
import { Modal } from '../../components/ui/Modal';
import { Spinner } from '../../components/ui/Loader';

const SCRAP_STATUS_OPTIONS = [
    { value: '', label: 'All Status' },
    { value: 'draft', label: 'Draft' },
    { value: 'confirmed', label: 'Confirmed' },
    { value: 'done', label: 'Done' },
    { value: 'cancelled', label: 'Cancelled' },
];

const SCRAP_TYPE_OPTIONS = [
    { value: 'raw_material', label: 'Raw Material' },
    { value: 'production', label: 'Production' },
    { value: 'finished_goods', label: 'Finished Goods' },
    { value: 'return', label: 'Return' },
];

export default function ScrapOrdersPage() {
    const {
        data: scraps, loading, saving, error, setError,
        currentPage, lastPage, total,
        search, filterValues,
        fetchData, handlePageChange, handleSearchChange, handleFilterChange,
        store, update, destroy, performAction,
    } = useCrudApi(scrapOrdersApi);

    const [view, setView] = useState('list');
    const [selectedId, setSelectedId] = useState(null);
    const [detail, setDetail] = useState(null);
    const [confirmAction, setConfirmAction] = useState(null);
    const [manufacturingOrders, setManufacturingOrders] = useState([]);
    const [products, setProducts] = useState([]);

    useEffect(() => {
        Promise.all([
            manufacturingOrdersApi.list({ per_page: 999 }),
            masterDataApi.list('products', { per_page: 999 }),
        ]).then(([mo, p]) => {
            const ex = (res) => { const d = res.data?.data; return Array.isArray(d) ? d : (d?.data || []); };
            setManufacturingOrders(ex(mo));
            setProducts(ex(p));
        }).catch(() => {});
    }, []);

    const loadDetail = async (id) => {
        try { const res = await scrapOrdersApi.show(id); setDetail(res.data.data); setSelectedId(id); setView('detail'); } catch {}
    };

    const handleAction = (action, id) => setConfirmAction({ action, id });
    const executeAction = async () => {
        if (!confirmAction) return;
        const { action, id } = confirmAction;
        const map = { confirm: scrapOrdersApi.confirm, process: scrapOrdersApi.process };
        const result = await performAction(map[action], id);
        if (result && view === 'detail' && id === selectedId) loadDetail(id);
        setConfirmAction(null);
    };

    const handleDelete = async (id) => {
        if (window.confirm('Delete this scrap order?')) { const ok = await destroy(id); if (ok && view === 'detail') setView('list'); }
    };

    const statusFilter = filterValues.status || '';

    if (view === 'create' || view === 'edit') {
        return <ScrapOrderForm isEdit={view === 'edit'} itemId={selectedId}
            manufacturingOrders={manufacturingOrders} products={products}
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
                            <h1 className="text-2xl font-bold text-gray-900">{detail.scrap_number}</h1>
                            <StatusBadge status={detail.status} label={detail.status_label} />
                        </div>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        {s === 'draft' && <Button onClick={() => { setSelectedId(detail.id); setView('edit'); }} className="flex items-center gap-2 bg-blue-50 text-blue-700 hover:bg-blue-100"><Edit2 className="h-4 w-4" /> Edit</Button>}
                        {s === 'draft' && <Button onClick={() => handleAction('confirm', detail.id)} className="flex items-center gap-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100"><CheckCircle className="h-4 w-4" /> Confirm</Button>}
                        {s === 'confirmed' && <Button onClick={() => handleAction('process', detail.id)} className="flex items-center gap-2 bg-green-50 text-green-700 hover:bg-green-100"><Play className="h-4 w-4" /> Process</Button>}
                        {s === 'draft' && <Button onClick={() => handleDelete(detail.id)} className="flex items-center gap-2 bg-red-50 text-red-700 hover:bg-red-100"><Trash2 className="h-4 w-4" /> Delete</Button>}
                    </div>
                </div>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <InfoCard label="Scrap Number" value={detail.scrap_number} />
                    <InfoCard label="MO Reference" value={detail.manufacturing_order?.order_number || '-'} />
                    <InfoCard label="Product" value={detail.product?.name || '-'} />
                    <InfoCard label="Scrap Type" value={detail.scrap_type || '-'} />
                    <InfoCard label="Quantity" value={detail.quantity} />
                    <InfoCard label="Unit Cost" value={formatCurrency(detail.unit_cost)} />
                    <InfoCard label="Total Cost" value={formatCurrency(detail.total_cost)} />
                    <InfoCard label="Scrap Date" value={formatDate(detail.scrap_date)} />
                </div>
                {detail.notes && <div className="bg-white rounded-xl border border-gray-200 p-6"><h3 className="font-semibold text-gray-900 mb-2">Notes</h3><p className="text-gray-600 text-sm">{detail.notes}</p></div>}
            </div>
        );
    }

    return (
        <div className="space-y-6">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div><h1 className="text-2xl font-bold text-gray-900">Scrap Orders</h1><p className="text-sm text-gray-500">{total} orders total</p></div>
                <Button onClick={() => { setSelectedId(null); setView('create'); }} className="flex items-center gap-2"><Plus className="h-4 w-4" /> New Scrap Order</Button>
            </div>
            <div className="flex flex-col sm:flex-row gap-3">
                <div className="relative flex-1"><Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" /><input type="text" value={search} onChange={(e) => handleSearchChange(e.target.value)} placeholder="Search..." className="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" /></div>
                <select value={statusFilter} onChange={(e) => handleFilterChange({ ...filterValues, status: e.target.value })} className="rounded-lg border border-gray-300 px-3 py-2 text-sm">{SCRAP_STATUS_OPTIONS.map(o => <option key={o.value} value={o.value}>{o.label}</option>)}</select>
            </div>
            {loading ? <div className="flex justify-center py-20"><Spinner /></div> : (
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 border-b border-gray-200"><tr>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Scrap Number</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Product</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Type</th>
                                <th className="px-4 py-3 text-right font-medium text-gray-600">Qty</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                                <th className="px-4 py-3 text-center font-medium text-gray-600">Actions</th>
                            </tr></thead>
                            <tbody className="divide-y divide-gray-100">
                                {scraps.map((s) => (
                                    <tr key={s.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3"><button onClick={() => loadDetail(s.id)} className="font-medium text-indigo-600 hover:text-indigo-800">{s.scrap_number}</button></td>
                                        <td className="px-4 py-3 text-gray-800">{s.product?.name || '-'}</td>
                                        <td className="px-4 py-3 text-gray-600 capitalize">{s.scrap_type?.replace(/_/g, ' ') || '-'}</td>
                                        <td className="px-4 py-3 text-right">{s.quantity}</td>
                                        <td className="px-4 py-3"><StatusBadge status={s.status} label={s.status_label} /></td>
                                        <td className="px-4 py-3"><div className="flex items-center justify-center gap-1">
                                            <button onClick={() => loadDetail(s.id)} className="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100"><Eye className="h-4 w-4" /></button>
                                        </div></td>
                                    </tr>
                                ))}
                                {scraps.length === 0 && <tr><td colSpan={6} className="px-4 py-12 text-center text-gray-400">No scrap orders found.</td></tr>}
                            </tbody>
                        </table>
                    </div>
                </div>
            )}
            {confirmAction && (
                <Modal onClose={() => setConfirmAction(null)} title={`Confirm ${confirmAction.action}`}>
                    <p className="text-gray-600 mb-4">Are you sure you want to <strong>{confirmAction.action}</strong> this scrap order?</p>
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

function ScrapOrderForm({ isEdit, itemId, manufacturingOrders, products, onBack, onSave, saving, error }) {
    const [form, setForm] = useState({ manufacturing_order_id: '', product_id: '', scrap_type: 'production', quantity: 1, unit_cost: 0, scrap_date: new Date().toISOString().split('T')[0], notes: '' });
    const [loadingForm, setLoadingForm] = useState(isEdit);

    useEffect(() => {
        if (isEdit && itemId) {
            scrapOrdersApi.show(itemId).then(res => {
                const d = res.data.data;
                setForm({ manufacturing_order_id: d.manufacturing_order_id || '', product_id: d.product_id || '', scrap_type: d.scrap_type || 'production', quantity: d.quantity || 1, unit_cost: d.unit_cost || 0, scrap_date: d.scrap_date || '', notes: d.notes || '' });
                setLoadingForm(false);
            }).catch(() => setLoadingForm(false));
        }
    }, [isEdit, itemId]);

    if (loadingForm) return <div className="flex justify-center py-20"><Spinner /></div>;
    const errorMessages = error?.errors ? Object.values(error.errors).flat() : [];

    return (
        <div className="space-y-6">
            <div className="flex items-center gap-3">
                <button onClick={onBack} className="p-2 rounded-lg hover:bg-gray-100"><ArrowLeft className="h-5 w-5" /></button>
                <h1 className="text-2xl font-bold text-gray-900">{isEdit ? 'Edit Scrap Order' : 'New Scrap Order'}</h1>
            </div>
            {error && <div className="rounded-lg bg-red-50 p-4 text-red-700 text-sm"><p className="font-medium">{error.message}</p>{errorMessages.length > 0 && <ul className="mt-1 list-disc list-inside">{errorMessages.map((m, i) => <li key={i}>{m}</li>)}</ul>}</div>}
            <form onSubmit={async (e) => { e.preventDefault(); await onSave(form); }} className="space-y-6">
                <div className="bg-white rounded-xl border border-gray-200 p-6">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">MO Reference</label><select value={form.manufacturing_order_id} onChange={e => setForm(p => ({ ...p, manufacturing_order_id: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><option value="">Select MO</option>{manufacturingOrders.map(mo => <option key={mo.id} value={mo.id}>{mo.order_number}</option>)}</select></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Product *</label><select value={form.product_id} onChange={e => setForm(p => ({ ...p, product_id: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required><option value="">Select Product</option>{products.map(p => <option key={p.id} value={p.id}>{p.name}</option>)}</select></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Scrap Type *</label><select value={form.scrap_type} onChange={e => setForm(p => ({ ...p, scrap_type: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><option value="production">Production</option><option value="raw_material">Raw Material</option><option value="finished_goods">Finished Goods</option><option value="return">Return</option></select></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Quantity *</label><input type="number" min="0.01" step="0.01" value={form.quantity} onChange={e => setForm(p => ({ ...p, quantity: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required /></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Unit Cost</label><input type="number" min="0" step="0.01" value={form.unit_cost} onChange={e => setForm(p => ({ ...p, unit_cost: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Scrap Date *</label><input type="date" value={form.scrap_date} onChange={e => setForm(p => ({ ...p, scrap_date: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required /></div>
                        <div className="md:col-span-2"><label className="block text-sm font-medium text-gray-700 mb-1">Notes</label><input type="text" value={form.notes} onChange={e => setForm(p => ({ ...p, notes: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
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
