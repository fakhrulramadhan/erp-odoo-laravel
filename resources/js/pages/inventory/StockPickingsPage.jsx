import { useState, useEffect } from 'react';
import { Plus, Search, Eye, Edit2, Trash2, CheckCircle, XCircle, ArrowLeft, Truck, Package, ArrowDownCircle, ArrowUpCircle } from 'lucide-react';
import { stockPickingsApi, masterDataApi, settingsApi } from '../../api/endpoints';
import { useCrudApi } from '../../hooks/useCrudApi';
import { StatusBadge, formatDate } from '../../components/ui/StatusBadge';
import { Button } from '../../components/ui/Button';
import { Modal } from '../../components/ui/Modal';
import { Spinner } from '../../components/ui/Loader';

const PICKING_TYPE_OPTIONS = [
    { value: '', label: 'All Types' },
    { value: 'incoming', label: 'Incoming' },
    { value: 'outgoing', label: 'Outgoing' },
    { value: 'internal', label: 'Internal' },
];

const STATUS_OPTIONS = [
    { value: '', label: 'All Status' },
    { value: 'draft', label: 'Draft' },
    { value: 'waiting', label: 'Waiting' },
    { value: 'confirmed', label: 'Confirmed' },
    { value: 'assigned', label: 'Assigned' },
    { value: 'done', label: 'Done' },
    { value: 'cancelled', label: 'Cancelled' },
];

const TYPE_ICONS = {
    incoming: ArrowDownCircle,
    outgoing: ArrowUpCircle,
    internal: Package,
};
// s
export default function StockPickingsPage() {
    const {
        data: pickings, loading, saving, error, setError,
        currentPage, lastPage, total,
        search, filterValues,
        fetchData, handlePageChange, handleSearchChange, handleFilterChange,
        store, update, destroy, performAction,
    } = useCrudApi(stockPickingsApi);

    const [view, setView] = useState('list');
    const [selectedId, setSelectedId] = useState(null);
    const [detail, setDetail] = useState(null);
    const [detailLoading, setDetailLoading] = useState(false);
    const [confirmAction, setConfirmAction] = useState(null);
    const [warehouses, setWarehouses] = useState([]);
    const [locations, setLocations] = useState([]);
    const [products, setProducts] = useState([]);

    useEffect(() => {
        async function loadLookups() {
            try {
                const [w, l, p] = await Promise.all([
                    masterDataApi.list('warehouses', { per_page: 999 }),
                    masterDataApi.list('stock-locations', { per_page: 999 }),
                    masterDataApi.list('products', { per_page: 999 }),
                ]);
                const extract = (res) => { const d = res.data?.data; return Array.isArray(d) ? d : (d?.data || []); };
                setWarehouses(extract(w));
                setLocations(extract(l));
                setProducts(extract(p));
            } catch {}
        }
        loadLookups();
    }, []);

    const loadDetail = async (id) => {
        setDetailLoading(true);
        try {
            const res = await stockPickingsApi.show(id);
            setDetail(res.data.data);
            setSelectedId(id);
            setView('detail');
        } catch {} finally { setDetailLoading(false); }
    };

    const handleAction = (action, id) => setConfirmAction({ action, id });
    const executeAction = async () => {
        if (!confirmAction) return;
        const { action, id } = confirmAction;
        const map = { confirm: stockPickingsApi.confirm, validate: stockPickingsApi.validate, cancel: stockPickingsApi.cancel };
        const result = await performAction(map[action], id);
        if (result && view === 'detail') loadDetail(id);
        setConfirmAction(null);
    };

    const handleDelete = async (id) => {
        if (window.confirm('Delete this picking?')) {
            const ok = await destroy(id);
            if (ok && view === 'detail') setView('list');
        }
    };

    // ─── Form ──────────────────────────────────────
    const [form, setForm] = useState({
        picking_type: 'incoming', source_warehouse_id: '', dest_warehouse_id: '',
        scheduled_date: new Date().toISOString().split('T')[0], notes: '',
        stock_moves: [{ product_id: '', quantity: 1, source_location_id: '', dest_location_id: '' }],
    });
    const [loadingForm, setLoadingForm] = useState(false);

    const initForm = async (id) => {
        setLoadingForm(true);
        try {
            const res = await stockPickingsApi.show(id);
            const p = res.data.data;
            setForm({
                picking_type: p.picking_type || 'incoming',
                source_warehouse_id: p.source_warehouse_id || '', dest_warehouse_id: p.dest_warehouse_id || '',
                scheduled_date: p.scheduled_date || '', notes: p.notes || '',
                stock_moves: (p.stock_moves || []).map(m => ({
                    product_id: m.product_id, quantity: m.quantity || m.reserved_qty,
                    source_location_id: m.source_location_id || '', dest_location_id: m.dest_location_id || '',
                })),
            });
            setSelectedId(id);
            setView('form');
        } catch {} finally { setLoadingForm(false); }
    };

    const resetForm = () => {
        setForm({ picking_type: 'incoming', source_warehouse_id: '', dest_warehouse_id: '', scheduled_date: new Date().toISOString().split('T')[0], notes: '', stock_moves: [{ product_id: '', quantity: 1, source_location_id: '', dest_location_id: '' }] });
        setSelectedId(null);
    };

    const updateField = (f, v) => setForm(p => ({ ...p, [f]: v }));
    const updateMove = (i, f, v) => { setForm(p => { const m = [...p.stock_moves]; m[i] = { ...m[i], [f]: f === 'quantity' ? (v === '' ? '' : Number(v)) : v }; return { ...p, stock_moves: m }; }); };
    const addMove = () => setForm(p => ({ ...p, stock_moves: [...p.stock_moves, { product_id: '', quantity: 1, source_location_id: '', dest_location_id: '' }] }));
    const removeMove = (i) => { if (form.stock_moves.length <= 1) return; setForm(p => ({ ...p, stock_moves: p.stock_moves.filter((_, idx) => idx !== i) })); };

    if (view === 'form') {
        const isEdit = !!selectedId;
        const errorMessages = error?.errors ? Object.values(error.errors).flat() : [];
        return (
            <div className="space-y-6">
                <div className="flex items-center gap-3">
                    <button onClick={() => { setView(selectedId ? 'detail' : 'list'); resetForm(); setError(null); }} className="p-2 rounded-lg hover:bg-gray-100"><ArrowLeft className="h-5 w-5" /></button>
                    <h1 className="text-2xl font-bold text-gray-900">{isEdit ? 'Edit Picking' : 'New Picking'}</h1>
                </div>
                {error && <div className="rounded-lg bg-red-50 p-4 text-red-700 text-sm">
                    <p className="font-medium">{error.message || 'Error'}</p>
                    {errorMessages.length > 0 && <ul className="mt-1 list-disc list-inside">{errorMessages.map((m, i) => <li key={i}>{m}</li>)}</ul>}
                </div>}
                <form onSubmit={async (e) => {
                    e.preventDefault();
                    // Transform form data to match backend field names
                    const payload = {
                        picking_type: form.picking_type,
                        source_location_id: form.source_warehouse_id || undefined,
                        destination_location_id: form.dest_warehouse_id || undefined,
                        scheduled_date: form.scheduled_date,
                        notes: form.notes,
                        moves: (form.stock_moves || []).map(m => ({
                            product_id: m.product_id,
                            quantity: Number(m.quantity) || 1,
                            source_location_id: m.source_location_id || undefined,
                            destination_location_id: m.dest_location_id || undefined,
                        })),
                    };
                    let ok;
                    if (isEdit) { ok = await update(selectedId, payload); if (ok) loadDetail(selectedId); }
                    else { ok = await store(payload); if (ok) { setView('list'); resetForm(); } }
                }} className="space-y-6">
                    <div className="bg-white rounded-xl border border-gray-200 p-6">
                        <h3 className="font-semibold text-gray-900 mb-4">Picking Information</h3>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                                <select value={form.picking_type} onChange={e => updateField('picking_type', e.target.value)} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    <option value="incoming">Incoming</option>
                                    <option value="outgoing">Outgoing</option>
                                    <option value="internal">Internal Transfer</option>
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Source Warehouse</label>
                                <select value={form.source_warehouse_id} onChange={e => updateField('source_warehouse_id', e.target.value)} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    <option value="">Select</option>
                                    {warehouses.map(w => <option key={w.id} value={w.id}>{w.name}</option>)}
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Destination Warehouse</label>
                                <select value={form.dest_warehouse_id} onChange={e => updateField('dest_warehouse_id', e.target.value)} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    <option value="">Select</option>
                                    {warehouses.map(w => <option key={w.id} value={w.id}>{w.name}</option>)}
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Scheduled Date *</label>
                                <input type="date" value={form.scheduled_date} onChange={e => updateField('scheduled_date', e.target.value)} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required />
                            </div>
                            <div className="md:col-span-2">
                                <label className="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                <input type="text" value={form.notes} onChange={e => updateField('notes', e.target.value)} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                            </div>
                        </div>
                    </div>
                    <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <div className="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                            <h3 className="font-semibold text-gray-900">Stock Moves</h3>
                            <Button type="button" onClick={addMove} className="flex items-center gap-2 text-sm bg-indigo-50 text-indigo-700 hover:bg-indigo-100"><Plus className="h-4 w-4" /> Add Move</Button>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead className="bg-gray-50"><tr>
                                    <th className="px-3 py-3 text-left font-medium text-gray-600 w-8">#</th>
                                    <th className="px-3 py-3 text-left font-medium text-gray-600">Product</th>
                                    <th className="px-3 py-3 text-right font-medium text-gray-600 w-24">Qty</th>
                                    <th className="px-3 py-3 text-left font-medium text-gray-600">Source Location</th>
                                    <th className="px-3 py-3 text-left font-medium text-gray-600">Dest Location</th>
                                    <th className="px-3 py-3 w-12"></th>
                                </tr></thead>
                                <tbody className="divide-y divide-gray-100">
                                    {form.stock_moves.map((move, i) => (
                                        <tr key={i}>
                                            <td className="px-3 py-2 text-gray-500">{i + 1}</td>
                                            <td className="px-3 py-2">
                                                <select value={move.product_id} onChange={e => updateMove(i, 'product_id', e.target.value)} className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" required>
                                                    <option value="">Select</option>
                                                    {products.map(p => <option key={p.id} value={p.id}>{p.name}</option>)}
                                                </select>
                                            </td>
                                            <td className="px-3 py-2"><input type="number" value={move.quantity} onChange={e => updateMove(i, 'quantity', e.target.value)} className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm text-right" min="0.01" step="any" required /></td>
                                            <td className="px-3 py-2">
                                                <select value={move.source_location_id} onChange={e => updateMove(i, 'source_location_id', e.target.value)} className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm">
                                                    <option value="">-</option>
                                                    {locations.map(l => <option key={l.id} value={l.id}>{l.name}</option>)}
                                                </select>
                                            </td>
                                            <td className="px-3 py-2">
                                                <select value={move.dest_location_id} onChange={e => updateMove(i, 'dest_location_id', e.target.value)} className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm">
                                                    <option value="">-</option>
                                                    {locations.map(l => <option key={l.id} value={l.id}>{l.name}</option>)}
                                                </select>
                                            </td>
                                            <td className="px-3 py-2"><button type="button" onClick={() => removeMove(i)} className="p-1 rounded text-red-400 hover:text-red-600" disabled={form.stock_moves.length <= 1}><Trash2 className="h-4 w-4" /></button></td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div className="flex justify-end gap-3">
                        <Button type="button" onClick={() => { setView(selectedId ? 'detail' : 'list'); resetForm(); setError(null); }} className="bg-gray-100 text-gray-700 hover:bg-gray-200">Cancel</Button>
                        <Button type="submit" disabled={saving}>{saving ? 'Saving...' : (isEdit ? 'Update' : 'Create')}</Button>
                    </div>
                </form>
            </div>
        );
    }

    if (view === 'detail' && detail) {
        const status = detail.status;
        const canEdit = status === 'draft';
        const canConfirm = status === 'draft';
        const canValidate = status === 'confirmed' || status === 'assigned';
        const canCancel = ['draft', 'waiting', 'confirmed'].includes(status);
        const TypeIcon = TYPE_ICONS[detail.picking_type] || Package;
        return (
            <div className="space-y-6">
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div className="flex items-center gap-3">
                        <button onClick={() => { setView('list'); setDetail(null); }} className="p-2 rounded-lg hover:bg-gray-100"><ArrowLeft className="h-5 w-5" /></button>
                        <div className="flex items-center gap-2">
                            <TypeIcon className={`h-6 w-6 ${detail.picking_type === 'incoming' ? 'text-green-600' : detail.picking_type === 'outgoing' ? 'text-red-600' : 'text-blue-600'}`} />
                            <div><h1 className="text-2xl font-bold text-gray-900">{detail.picking_number}</h1><StatusBadge status={detail.status} label={detail.status_label} /></div>
                        </div>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        {canEdit && <Button onClick={() => initForm(detail.id)} className="flex items-center gap-2 bg-blue-50 text-blue-700 hover:bg-blue-100"><Edit2 className="h-4 w-4" /> Edit</Button>}
                        {canConfirm && <Button onClick={() => handleAction('confirm', detail.id)} className="flex items-center gap-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100"><CheckCircle className="h-4 w-4" /> Confirm</Button>}
                        {canValidate && <Button onClick={() => handleAction('validate', detail.id)} className="flex items-center gap-2 bg-green-50 text-green-700 hover:bg-green-100"><CheckCircle className="h-4 w-4" /> Validate</Button>}
                        {canCancel && <Button onClick={() => handleAction('cancel', detail.id)} className="flex items-center gap-2 bg-red-50 text-red-700 hover:bg-red-100"><XCircle className="h-4 w-4" /> Cancel</Button>}
                        {canEdit && <Button onClick={() => handleDelete(detail.id)} className="flex items-center gap-2 bg-red-50 text-red-700 hover:bg-red-100"><Trash2 className="h-4 w-4" /> Delete</Button>}
                    </div>
                </div>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <InfoCard label="Type" value={<span className="capitalize">{detail.picking_type}</span>} />
                    <InfoCard label="Scheduled Date" value={formatDate(detail.scheduled_date)} />
                    <InfoCard label="Source" value={detail.source_warehouse?.name || '-'} />
                    <InfoCard label="Destination" value={detail.dest_warehouse?.name || '-'} />
                    <InfoCard label="Created By" value={detail.creator?.name || '-'} />
                    <InfoCard label="Notes" value={detail.notes || '-'} />
                </div>
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="px-6 py-4 border-b border-gray-200"><h3 className="font-semibold text-gray-900">Stock Moves</h3></div>
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50"><tr>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">#</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Product</th>
                                <th className="px-4 py-3 text-right font-medium text-gray-600">Reserved</th>
                                <th className="px-4 py-3 text-right font-medium text-gray-600">Done</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                            </tr></thead>
                            <tbody className="divide-y divide-gray-100">
                                {(detail.stock_moves || []).map((m, i) => (
                                    <tr key={i} className="hover:bg-gray-50">
                                        <td className="px-4 py-3 text-gray-500">{i + 1}</td>
                                        <td className="px-4 py-3 font-medium">{m.product?.name || '-'}</td>
                                        <td className="px-4 py-3 text-right">{m.reserved_qty || 0}</td>
                                        <td className="px-4 py-3 text-right font-medium">{m.done_qty || 0}</td>
                                        <td className="px-4 py-3"><StatusBadge status={m.status} /></td>
                                    </tr>
                                ))}
                                {(detail.stock_moves || []).length === 0 && <tr><td colSpan={5} className="px-4 py-8 text-center text-gray-400">No moves</td></tr>}
                            </tbody>
                        </table>
                    </div>
                </div>
                {confirmAction && <Modal onClose={() => setConfirmAction(null)} title={`Confirm ${confirmAction.action}`}>
                    <p className="text-gray-600 mb-4">Are you sure you want to <strong>{confirmAction.action}</strong> this picking?</p>
                    {confirmAction.action === 'validate' && <p className="text-sm text-orange-600 mb-4">⚠ This will update stock quantities.</p>}
                    <div className="flex justify-end gap-3">
                        <Button onClick={() => setConfirmAction(null)} className="bg-gray-100 text-gray-700 hover:bg-gray-200">Cancel</Button>
                        <Button onClick={executeAction} disabled={saving}>{saving ? 'Processing...' : 'Confirm'}</Button>
                    </div>
                </Modal>}
            </div>
        );
    }

    // ─── LIST ──────────────────────────────────────
    return (
        <div className="space-y-6">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div><h1 className="text-2xl font-bold text-gray-900">Stock Pickings</h1><p className="text-sm text-gray-500">{total} pickings total</p></div>
                <Button onClick={() => { resetForm(); setView('form'); }} className="flex items-center gap-2"><Plus className="h-4 w-4" /> New Picking</Button>
            </div>
            <div className="flex flex-col sm:flex-row gap-3">
                <div className="relative flex-1">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <input type="text" value={search} onChange={e => handleSearchChange(e.target.value)} placeholder="Search pickings..." className="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm" />
                </div>
                <select value={filterValues.picking_type || ''} onChange={e => handleFilterChange({ ...filterValues, picking_type: e.target.value })} className="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    {PICKING_TYPE_OPTIONS.map(o => <option key={o.value} value={o.value}>{o.label}</option>)}
                </select>
                <select value={filterValues.status || ''} onChange={e => handleFilterChange({ ...filterValues, status: e.target.value })} className="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    {STATUS_OPTIONS.map(o => <option key={o.value} value={o.value}>{o.label}</option>)}
                </select>
            </div>
            {loading ? <div className="flex justify-center py-20"><Spinner /></div> : (
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 border-b border-gray-200"><tr>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Number</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Type</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Scheduled</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Source</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Destination</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                                <th className="px-4 py-3 text-center font-medium text-gray-600">Actions</th>
                            </tr></thead>
                            <tbody className="divide-y divide-gray-100">
                                {pickings.map(p => {
                                    const Icon = TYPE_ICONS[p.picking_type] || Package;
                                    return (
                                        <tr key={p.id} className="hover:bg-gray-50">
                                            <td className="px-4 py-3"><button onClick={() => loadDetail(p.id)} className="font-medium text-indigo-600 hover:text-indigo-800">{p.picking_number}</button></td>
                                            <td className="px-4 py-3"><div className="flex items-center gap-2"><Icon className="h-4 w-4 text-gray-500" /><span className="capitalize">{p.picking_type}</span></div></td>
                                            <td className="px-4 py-3 text-gray-600">{formatDate(p.scheduled_date)}</td>
                                            <td className="px-4 py-3">{p.source_warehouse?.name || '-'}</td>
                                            <td className="px-4 py-3">{p.dest_warehouse?.name || '-'}</td>
                                            <td className="px-4 py-3"><StatusBadge status={p.status} label={p.status_label} /></td>
                                            <td className="px-4 py-3"><div className="flex items-center justify-center gap-1">
                                                <button onClick={() => loadDetail(p.id)} className="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100"><Eye className="h-4 w-4" /></button>
                                                {p.status === 'draft' && <>
                                                    <button onClick={() => initForm(p.id)} className="p-1.5 rounded-lg text-blue-500 hover:bg-blue-50"><Edit2 className="h-4 w-4" /></button>
                                                    <button onClick={() => handleDelete(p.id)} className="p-1.5 rounded-lg text-red-500 hover:bg-red-50"><Trash2 className="h-4 w-4" /></button>
                                                </>}
                                            </div></td>
                                        </tr>
                                    );
                                })}
                                {pickings.length === 0 && <tr><td colSpan={7} className="px-4 py-12 text-center text-gray-400">No pickings found.</td></tr>}
                            </tbody>
                        </table>
                    </div>
                    {lastPage > 1 && <div className="flex items-center justify-between border-t border-gray-200 px-4 py-3">
                        <p className="text-sm text-gray-500">Page {currentPage} of {lastPage}</p>
                        <div className="flex gap-1">
                            {Array.from({ length: Math.min(lastPage, 7) }, (_, i) => i + 1).map(p => (
                                <button key={p} onClick={() => handlePageChange(p)} className={`px-3 py-1 rounded-lg text-sm ${p === currentPage ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100'}`}>{p}</button>
                            ))}
                        </div>
                    </div>}
                </div>
            )}
        </div>
    );
}

function InfoCard({ label, value }) {
    return <div className="bg-white rounded-xl border border-gray-200 p-4"><p className="text-xs text-gray-500 uppercase tracking-wide mb-1">{label}</p><p className="text-sm font-medium text-gray-900">{value}</p></div>;
}
