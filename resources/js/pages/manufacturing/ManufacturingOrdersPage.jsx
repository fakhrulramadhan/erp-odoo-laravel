import { useState, useEffect } from 'react';
import {
    Plus, Search, Eye, Edit2, Trash2, CheckCircle, XCircle,
    Factory, ArrowLeft, Package, Play, Flag, XSquare, Send, Lock
} from 'lucide-react';
import { manufacturingOrdersApi, bomsApi, routingsApi, workCentersApi, masterDataApi } from '../../api/endpoints';
import { useCrudApi } from '../../hooks/useCrudApi';
import { StatusBadge, formatCurrency, formatDate } from '../../components/ui/StatusBadge';
import { Button } from '../../components/ui/Button';
import { Modal } from '../../components/ui/Modal';
import { Spinner } from '../../components/ui/Loader';

const MO_STATUS_OPTIONS = [
    { value: '', label: 'All Status' },
    { value: 'draft', label: 'Draft' },
    { value: 'confirmed', label: 'Confirmed' },
    { value: 'material_reserved', label: 'Material Reserved' },
    { value: 'in_production', label: 'In Production' },
    { value: 'quality_check', label: 'Quality Check' },
    { value: 'finished', label: 'Finished' },
    { value: 'closed', label: 'Closed' },
    { value: 'cancelled', label: 'Cancelled' },
];

export default function ManufacturingOrdersPage() {
    const {
        data: orders, loading, saving, error, setError,
        currentPage, lastPage, total,
        search, filterValues,
        fetchData, handlePageChange, handleSearchChange, handleFilterChange,
        store, update, destroy, performAction,
    } = useCrudApi(manufacturingOrdersApi);

    const [view, setView] = useState('list');
    const [selectedId, setSelectedId] = useState(null);
    const [detail, setDetail] = useState(null);
    const [detailLoading, setDetailLoading] = useState(false);
    const [confirmAction, setConfirmAction] = useState(null);

    // Lookups
    const [products, setProducts] = useState([]);
    const [boms, setBoms] = useState([]);
    const [routings, setRoutings] = useState([]);
    const [workCenters, setWorkCenters] = useState([]);

    useEffect(() => {
        async function loadLookups() {
            try {
                const [p, b, r, w] = await Promise.all([
                    masterDataApi.list('products', { per_page: 999 }),
                    bomsApi.list({ per_page: 999 }),
                    routingsApi.list({ per_page: 999 }),
                    workCentersApi.list({ per_page: 999 }),
                ]);
                setProducts(extractItems(p));
                setBoms(extractItems(b));
                setRoutings(extractItems(r));
                setWorkCenters(extractItems(w));
            } catch {}
        }
        loadLookups();
    }, []);

    const extractItems = (res) => {
        const p = res.data?.data;
        return Array.isArray(p) ? p : (p?.data || []);
    };

    const loadDetail = async (id) => {
        setDetailLoading(true);
        try {
            const res = await manufacturingOrdersApi.show(id);
            setDetail(res.data.data);
            setSelectedId(id);
            setView('detail');
        } catch {} finally {
            setDetailLoading(false);
        }
    };

    const handleAction = (action, id) => setConfirmAction({ action, id });

    const executeAction = async () => {
        if (!confirmAction) return;
        const { action, id } = confirmAction;
        const actionMap = {
            confirm: manufacturingOrdersApi.confirm,
            reserveMaterials: manufacturingOrdersApi.reserveMaterials,
            startProduction: manufacturingOrdersApi.startProduction,
            finishProduction: manufacturingOrdersApi.finishProduction,
            markFinished: manufacturingOrdersApi.markFinished,
            close: manufacturingOrdersApi.close,
            cancel: manufacturingOrdersApi.cancel,
        };
        const result = await performAction(actionMap[action], id);
        if (result && view === 'detail' && id === selectedId) loadDetail(id);
        setConfirmAction(null);
    };

    const handleDelete = async (id) => {
        if (window.confirm('Delete this manufacturing order?')) {
            const ok = await destroy(id);
            if (ok && view === 'detail') setView('list');
        }
    };

    const statusFilter = filterValues.status || '';
    const setStatusFilter = (val) => handleFilterChange({ ...filterValues, status: val });

    // ─── FORM VIEW ──────────────────────────────
    if (view === 'create' || view === 'edit') {
        return (
            <ManufacturingOrderForm
                isEdit={view === 'edit'}
                orderId={view === 'edit' ? selectedId : null}
                products={products}
                boms={boms}
                routings={routings}
                workCenters={workCenters}
                onBack={() => { setView(selectedId ? 'detail' : 'list'); setError(null); }}
                onSave={async (data) => {
                    let ok;
                    if (view === 'edit') ok = await update(selectedId, data);
                    else ok = await store(data);
                    if (ok) {
                        if (view === 'edit') loadDetail(selectedId);
                        else setView('list');
                    }
                    return ok;
                }}
                saving={saving}
                error={error}
            />
        );
    }

    // ─── DETAIL VIEW ────────────────────────────
    if (view === 'detail' && detail) {
        return (
            <ManufacturingOrderDetail
                order={detail}
                onBack={() => { setView('list'); setDetail(null); }}
                onEdit={() => setView('edit')}
                onAction={handleAction}
                onDelete={handleDelete}
                loading={detailLoading}
                reload={() => loadDetail(detail.id)}
            />
        );
    }

    // ─── LIST VIEW ──────────────────────────────
    return (
        <div className="space-y-6">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Manufacturing Orders</h1>
                    <p className="text-sm text-gray-500">{total} orders total</p>
                </div>
                <Button onClick={() => { setSelectedId(null); setView('create'); }} className="flex items-center gap-2">
                    <Plus className="h-4 w-4" /> New Manufacturing Order
                </Button>
            </div>

            <div className="flex flex-col sm:flex-row gap-3">
                <div className="relative flex-1">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <input
                        type="text" value={search}
                        onChange={(e) => handleSearchChange(e.target.value)}
                        placeholder="Search orders..."
                        className="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                    />
                </div>
                <select value={statusFilter} onChange={(e) => setStatusFilter(e.target.value)}
                    className="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    {MO_STATUS_OPTIONS.map(o => <option key={o.value} value={o.value}>{o.label}</option>)}
                </select>
            </div>

            {loading ? (
                <div className="flex justify-center py-20"><Spinner /></div>
            ) : error && typeof error === 'string' ? (
                <div className="rounded-lg bg-red-50 p-4 text-red-700">{error}</div>
            ) : (
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">MO Number</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Product</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">BOM</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">Qty</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Date</th>
                                    <th className="px-4 py-3 text-center font-medium text-gray-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {orders.map((mo) => (
                                    <tr key={mo.id} className="hover:bg-gray-50 transition-colors">
                                        <td className="px-4 py-3">
                                            <button onClick={() => loadDetail(mo.id)} className="font-medium text-indigo-600 hover:text-indigo-800">
                                                {mo.order_number}
                                            </button>
                                        </td>
                                        <td className="px-4 py-3 text-gray-800">{mo.product?.name || '-'}</td>
                                        <td className="px-4 py-3 text-gray-600">{mo.bom?.name || '-'}</td>
                                        <td className="px-4 py-3 text-right">{mo.quantity} {mo.product?.uom?.symbol || ''}</td>
                                        <td className="px-4 py-3"><StatusBadge status={mo.status} label={mo.status_label} /></td>
                                        <td className="px-4 py-3 text-gray-600">{formatDate(mo.order_date || mo.created_at)}</td>
                                        <td className="px-4 py-3">
                                            <div className="flex items-center justify-center gap-1">
                                                <button onClick={() => loadDetail(mo.id)} className="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100" title="View">
                                                    <Eye className="h-4 w-4" />
                                                </button>
                                                {mo.status === 'draft' && (
                                                    <>
                                                        <button onClick={() => { setSelectedId(mo.id); setView('edit'); }} className="p-1.5 rounded-lg text-blue-500 hover:bg-blue-50" title="Edit">
                                                            <Edit2 className="h-4 w-4" />
                                                        </button>
                                                        <button onClick={() => handleDelete(mo.id)} className="p-1.5 rounded-lg text-red-500 hover:bg-red-50" title="Delete">
                                                            <Trash2 className="h-4 w-4" />
                                                        </button>
                                                    </>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                                {orders.length === 0 && (
                                    <tr><td colSpan={7} className="px-4 py-12 text-center text-gray-400">No manufacturing orders found.</td></tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                    {lastPage > 1 && (
                        <div className="flex items-center justify-between border-t border-gray-200 px-4 py-3">
                            <p className="text-sm text-gray-500">Page {currentPage} of {lastPage} ({total} total)</p>
                            <div className="flex gap-1">
                                {Array.from({ length: Math.min(lastPage, 7) }, (_, i) => i + 1).map(page => (
                                    <button key={page} onClick={() => handlePageChange(page)}
                                        className={`px-3 py-1 rounded-lg text-sm ${page === currentPage ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100'}`}>
                                        {page}
                                    </button>
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            )}

            {confirmAction && (
                <Modal onClose={() => setConfirmAction(null)} title={`Confirm ${confirmAction.action}`}>
                    <p className="text-gray-600 mb-4">
                        Are you sure you want to <strong>{confirmAction.action.replace(/([A-Z])/g, ' $1').toLowerCase()}</strong> this manufacturing order?
                    </p>
                    <div className="flex justify-end gap-3">
                        <Button onClick={() => setConfirmAction(null)} className="bg-gray-100 text-gray-700 hover:bg-gray-200">Cancel</Button>
                        <Button onClick={executeAction} disabled={saving}>{saving ? 'Processing...' : 'Confirm'}</Button>
                    </div>
                </Modal>
            )}
        </div>
    );
}

// ─── DETAIL VIEW ─────────────────────────────────
function ManufacturingOrderDetail({ order, onBack, onEdit, onAction, onDelete, loading, reload }) {
    if (loading) return <div className="flex justify-center py-20"><Spinner /></div>;

    const s = order.status;
    const canEdit = s === 'draft';
    const canConfirm = s === 'draft';
    const canReserve = s === 'confirmed';
    const canStart = s === 'material_reserved';
    const canFinish = s === 'in_production';
    const canMarkFinished = s === 'quality_check';
    const canClose = s === 'finished';
    const canCancel = ['draft', 'confirmed', 'material_reserved'].includes(s);

    return (
        <div className="space-y-6">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div className="flex items-center gap-3">
                    <button onClick={onBack} className="p-2 rounded-lg hover:bg-gray-100"><ArrowLeft className="h-5 w-5" /></button>
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">{order.order_number}</h1>
                        <StatusBadge status={order.status} label={order.status_label} />
                    </div>
                </div>
                <div className="flex flex-wrap gap-2">
                    {canEdit && <Button onClick={onEdit} className="flex items-center gap-2 bg-blue-50 text-blue-700 hover:bg-blue-100"><Edit2 className="h-4 w-4" /> Edit</Button>}
                    {canConfirm && <Button onClick={() => onAction('confirm', order.id)} className="flex items-center gap-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100"><Send className="h-4 w-4" /> Confirm</Button>}
                    {canReserve && <Button onClick={() => onAction('reserveMaterials', order.id)} className="flex items-center gap-2 bg-teal-50 text-teal-700 hover:bg-teal-100"><Package className="h-4 w-4" /> Reserve Materials</Button>}
                    {canStart && <Button onClick={() => onAction('startProduction', order.id)} className="flex items-center gap-2 bg-blue-50 text-blue-700 hover:bg-blue-100"><Play className="h-4 w-4" /> Start Production</Button>}
                    {canFinish && <Button onClick={() => onAction('finishProduction', order.id)} className="flex items-center gap-2 bg-green-50 text-green-700 hover:bg-green-100"><Flag className="h-4 w-4" /> Finish Production</Button>}
                    {canMarkFinished && <Button onClick={() => onAction('markFinished', order.id)} className="flex items-center gap-2 bg-green-50 text-green-700 hover:bg-green-100"><CheckCircle className="h-4 w-4" /> Mark Finished</Button>}
                    {canClose && <Button onClick={() => onAction('close', order.id)} className="flex items-center gap-2 bg-gray-50 text-gray-700 hover:bg-gray-100"><Lock className="h-4 w-4" /> Close</Button>}
                    {canCancel && <Button onClick={() => onAction('cancel', order.id)} className="flex items-center gap-2 bg-red-50 text-red-700 hover:bg-red-100"><XCircle className="h-4 w-4" /> Cancel</Button>}
                    {canEdit && <Button onClick={() => onDelete(order.id)} className="flex items-center gap-2 bg-red-50 text-red-700 hover:bg-red-100"><Trash2 className="h-4 w-4" /> Delete</Button>}
                </div>
            </div>

            {/* Info Grid */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <InfoCard label="Product" value={order.product?.name || '-'} />
                <InfoCard label="BOM" value={order.bom?.name || '-'} />
                <InfoCard label="Routing" value={order.routing?.name || '-'} />
                <InfoCard label="Quantity" value={`${order.quantity} ${order.product?.uom?.symbol || ''}`} />
                <InfoCard label="Work Center" value={order.work_center?.name || '-'} />
                <InfoCard label="Order Date" value={formatDate(order.order_date)} />
                <InfoCard label="Planned Start" value={formatDate(order.planned_start_date)} />
                <InfoCard label="Planned End" value={formatDate(order.planned_end_date)} />
                <InfoCard label="Created By" value={order.creator?.name || '-'} />
            </div>

            {/* Production Costs */}
            {order.production_costs && order.production_costs.length > 0 && (
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="px-6 py-4 border-b border-gray-200">
                        <h3 className="font-semibold text-gray-900">Production Costs</h3>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Cost Type</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">Amount</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Description</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {order.production_costs.map((c) => (
                                    <tr key={c.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3 capitalize">{c.cost_type?.replace(/_/g, ' ')}</td>
                                        <td className="px-4 py-3 text-right font-medium">{formatCurrency(c.amount)}</td>
                                        <td className="px-4 py-3 text-gray-600">{c.description || '-'}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            )}

            {/* Lines */}
            {order.lines && order.lines.length > 0 && (
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="px-6 py-4 border-b border-gray-200">
                        <h3 className="font-semibold text-gray-900">Order Lines</h3>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Product</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">Qty Required</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">Qty Consumed</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Type</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {order.lines.map((l) => (
                                    <tr key={l.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3 font-medium">{l.product?.name || '-'}</td>
                                        <td className="px-4 py-3 text-right">{l.quantity_required}</td>
                                        <td className="px-4 py-3 text-right">{l.quantity_consumed || 0}</td>
                                        <td className="px-4 py-3"><StatusBadge status={l.line_type} label={l.line_type === 'component' ? 'Component' : 'By-product'} /></td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            )}

            {/* Notes */}
            {order.notes && (
                <div className="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 className="font-semibold text-gray-900 mb-2">Notes</h3>
                    <p className="text-gray-600 text-sm">{order.notes}</p>
                </div>
            )}
        </div>
    );
}

function InfoCard({ label, value }) {
    return (
        <div className="bg-white rounded-xl border border-gray-200 p-4">
            <p className="text-xs text-gray-500 uppercase tracking-wide mb-1">{label}</p>
            <p className="text-sm font-medium text-gray-900">{value}</p>
        </div>
    );
}

// ─── CREATE / EDIT FORM ──────────────────────────
function ManufacturingOrderForm({ isEdit, orderId, products, boms, routings, workCenters, onBack, onSave, saving, error }) {
    const [form, setForm] = useState({
        product_id: '', bom_id: '', routing_id: '', work_center_id: '',
        quantity: 1,
        order_date: new Date().toISOString().split('T')[0],
        planned_start_date: '', planned_end_date: '', notes: '',
        lines: [{ product_id: '', quantity_required: 1, line_type: 'component', description: '' }],
    });
    const [loadingForm, setLoadingForm] = useState(isEdit);

    useEffect(() => {
        if (isEdit && orderId) {
            manufacturingOrdersApi.show(orderId).then(res => {
                const mo = res.data.data;
                setForm({
                    product_id: mo.product_id || '',
                    bom_id: mo.bom_id || '',
                    routing_id: mo.routing_id || '',
                    work_center_id: mo.work_center_id || '',
                    quantity: mo.quantity || 1,
                    order_date: mo.order_date || '',
                    planned_start_date: mo.planned_start_date || '',
                    planned_end_date: mo.planned_end_date || '',
                    notes: mo.notes || '',
                    lines: (mo.lines || []).map(l => ({
                        product_id: l.product_id,
                        quantity_required: l.quantity_required,
                        line_type: l.line_type || 'component',
                        description: l.description || '',
                    })),
                });
                setLoadingForm(false);
            }).catch(() => setLoadingForm(false));
        }
    }, [isEdit, orderId]);

    const updateField = (field, value) => setForm(prev => ({ ...prev, [field]: value }));
    const updateLine = (idx, field, value) => {
        setForm(prev => {
            const lines = [...prev.lines];
            lines[idx] = { ...lines[idx], [field]: value };
            return { ...prev, lines };
        });
    };
    const addLine = () => setForm(prev => ({
        ...prev,
        lines: [...prev.lines, { product_id: '', quantity_required: 1, line_type: 'component', description: '' }],
    }));
    const removeLine = (idx) => {
        if (form.lines.length <= 1) return;
        setForm(prev => ({ ...prev, lines: prev.lines.filter((_, i) => i !== idx) }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        await onSave(form);
    };

    if (loadingForm) return <div className="flex justify-center py-20"><Spinner /></div>;

    const errorMessages = error?.errors ? Object.values(error.errors).flat() : [];

    return (
        <div className="space-y-6">
            <div className="flex items-center gap-3">
                <button onClick={onBack} className="p-2 rounded-lg hover:bg-gray-100"><ArrowLeft className="h-5 w-5" /></button>
                <h1 className="text-2xl font-bold text-gray-900">{isEdit ? `Edit ${orderId}` : 'New Manufacturing Order'}</h1>
            </div>

            {error && (
                <div className="rounded-lg bg-red-50 p-4 text-red-700 text-sm">
                    <p className="font-medium">{error.message || 'Validation error'}</p>
                    {errorMessages.length > 0 && <ul className="mt-1 list-disc list-inside">{errorMessages.map((m, i) => <li key={i}>{m}</li>)}</ul>}
                </div>
            )}

            <form onSubmit={handleSubmit} className="space-y-6">
                <div className="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 className="font-semibold text-gray-900 mb-4">Order Information</h3>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Product *</label>
                            <select value={form.product_id} onChange={e => updateField('product_id', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" required>
                                <option value="">Select Product</option>
                                {products.map(p => <option key={p.id} value={p.id}>{p.name}</option>)}
                            </select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">BOM *</label>
                            <select value={form.bom_id} onChange={e => updateField('bom_id', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" required>
                                <option value="">Select BOM</option>
                                {boms.map(b => <option key={b.id} value={b.id}>{b.name}</option>)}
                            </select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Routing</label>
                            <select value={form.routing_id} onChange={e => updateField('routing_id', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                <option value="">Select Routing</option>
                                {routings.map(r => <option key={r.id} value={r.id}>{r.name}</option>)}
                            </select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Work Center</label>
                            <select value={form.work_center_id} onChange={e => updateField('work_center_id', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                <option value="">Select Work Center</option>
                                {workCenters.map(w => <option key={w.id} value={w.id}>{w.name}</option>)}
                            </select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Quantity *</label>
                            <input type="number" min="0.01" step="0.01" value={form.quantity} onChange={e => updateField('quantity', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" required />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Order Date *</label>
                            <input type="date" value={form.order_date} onChange={e => updateField('order_date', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" required />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Planned Start</label>
                            <input type="date" value={form.planned_start_date} onChange={e => updateField('planned_start_date', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Planned End</label>
                            <input type="date" value={form.planned_end_date} onChange={e => updateField('planned_end_date', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <input type="text" value={form.notes} onChange={e => updateField('notes', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" placeholder="Optional notes..." />
                        </div>
                    </div>
                </div>

                {/* Lines */}
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 className="font-semibold text-gray-900">Component Lines</h3>
                        <Button type="button" onClick={addLine} className="flex items-center gap-2 text-sm bg-indigo-50 text-indigo-700 hover:bg-indigo-100">
                            <Plus className="h-4 w-4" /> Add Line
                        </Button>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Product *</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">Qty Required *</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Type</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Description</th>
                                    <th className="px-4 py-3 w-12"></th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {form.lines.map((line, idx) => (
                                    <tr key={idx}>
                                        <td className="px-4 py-2">
                                            <select value={line.product_id} onChange={e => updateLine(idx, 'product_id', e.target.value)}
                                                className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" required>
                                                <option value="">Select Product</option>
                                                {products.map(p => <option key={p.id} value={p.id}>{p.name}</option>)}
                                            </select>
                                        </td>
                                        <td className="px-4 py-2">
                                            <input type="number" min="0.01" step="0.01" value={line.quantity_required} onChange={e => updateLine(idx, 'quantity_required', e.target.value)}
                                                className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm text-right" required />
                                        </td>
                                        <td className="px-4 py-2">
                                            <select value={line.line_type} onChange={e => updateLine(idx, 'line_type', e.target.value)}
                                                className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm">
                                                <option value="component">Component</option>
                                                <option value="by_product">By-product</option>
                                            </select>
                                        </td>
                                        <td className="px-4 py-2">
                                            <input type="text" value={line.description} onChange={e => updateLine(idx, 'description', e.target.value)}
                                                className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" placeholder="Description" />
                                        </td>
                                        <td className="px-4 py-2">
                                            <button type="button" onClick={() => removeLine(idx)} className="p-1 rounded text-red-500 hover:bg-red-50"
                                                disabled={form.lines.length <= 1}><Trash2 className="h-4 w-4" /></button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>

                <div className="flex justify-end gap-3">
                    <Button type="button" onClick={onBack} className="bg-gray-100 text-gray-700 hover:bg-gray-200">Cancel</Button>
                    <Button type="submit" disabled={saving}>{saving ? 'Saving...' : (isEdit ? 'Update Order' : 'Create Order')}</Button>
                </div>
            </form>
        </div>
    );
}
