import { useState, useEffect, useMemo } from 'react';
import {
    Plus, Search, Eye, Edit2, Trash2, Send, CheckCircle, XCircle,
    ShoppingCart, ChevronDown, ChevronUp, ArrowLeft, MoreVertical
} from 'lucide-react';
import { purchaseOrdersApi } from '../../api/endpoints';
import { masterDataApi, settingsApi } from '../../api/endpoints';
import { useCrudApi } from '../../hooks/useCrudApi';
import { StatusBadge, formatCurrency, formatDate } from '../../components/ui/StatusBadge';
import { Button } from '../../components/ui/Button';
import { Modal } from '../../components/ui/Modal';
import { Spinner } from '../../components/ui/Loader';

// ─── PO Status Colors ───────────────────────────────
const PO_STATUS_OPTIONS = [
    { value: '', label: 'All Status' },
    { value: 'draft', label: 'Draft' },
    { value: 'waiting_approval', label: 'Waiting Approval' },
    { value: 'approved', label: 'Approved' },
    { value: 'ordered', label: 'Ordered' },
    { value: 'partial_received', label: 'Partial Received' },
    { value: 'received', label: 'Received' },
    { value: 'closed', label: 'Closed' },
    { value: 'cancelled', label: 'Cancelled' },
];

export default function PurchaseOrdersPage() {
    const {
        data: orders, loading, saving, error, setError,
        currentPage, lastPage, total,
        search, filterValues,
        fetchData, handlePageChange, handleSearchChange, handleFilterChange,
        store, update, destroy, performAction,
    } = useCrudApi(purchaseOrdersApi);

    const [view, setView] = useState('list'); // list | detail | create | edit
    const [selectedId, setSelectedId] = useState(null);
    const [detail, setDetail] = useState(null);
    const [detailLoading, setDetailLoading] = useState(false);
    const [confirmAction, setConfirmAction] = useState(null);
    const [menuOpenId, setMenuOpenId] = useState(null);

    // Lookups for forms
    const [vendors, setVendors] = useState([]);
    const [currencies, setCurrencies] = useState([]);
    const [warehouses, setWarehouses] = useState([]);
    const [products, setProducts] = useState([]);

    useEffect(() => {
        async function loadLookups() {
            try {
                const [v, c, w, p] = await Promise.all([
                    masterDataApi.list('vendors', { per_page: 999 }),
                    settingsApi.currencies({ per_page: 999 }),
                    masterDataApi.list('warehouses', { per_page: 999 }),
                    masterDataApi.list('products', { per_page: 999 }),
                ]);
                setVendors(extractItems(v));
                setCurrencies(extractItems(c));
                setWarehouses(extractItems(w));
                setProducts(extractItems(p));
            } catch {}
        }
        loadLookups();
    }, []);

    const extractItems = (res) => {
        const p = res.data?.data;
        return Array.isArray(p) ? p : (p?.data || []);
    };

    // ─── Detail View ───────────────────────────────
    const loadDetail = async (id) => {
        setDetailLoading(true);
        try {
            const res = await purchaseOrdersApi.show(id);
            setDetail(res.data.data);
            setSelectedId(id);
            setView('detail');
        } catch {} finally {
            setDetailLoading(false);
        }
    };

    // ─── Actions ───────────────────────────────────
    const handleAction = async (action, id) => {
        setConfirmAction({ action, id });
    };

    const executeAction = async () => {
        if (!confirmAction) return;
        const { action, id } = confirmAction;
        const actionMap = {
            submit: purchaseOrdersApi.submit,
            approve: purchaseOrdersApi.approve,
            cancel: purchaseOrdersApi.cancel,
            send: purchaseOrdersApi.sendToVendor,
        };
        const result = await performAction(actionMap[action], id);
        if (result && view === 'detail' && id === selectedId) {
            loadDetail(id);
        }
        setConfirmAction(null);
    };

    const handleDelete = async (id) => {
        if (window.confirm('Delete this purchase order?')) {
            const ok = await destroy(id);
            if (ok && view === 'detail') setView('list');
        }
    };

    // ─── Filter state ──────────────────────────────
    const statusFilter = filterValues.status || '';
    const setStatusFilter = (val) => handleFilterChange({ ...filterValues, status: val });

    // ─── RENDER ────────────────────────────────────
    if (view === 'create' || view === 'edit') {
        return (
            <PurchaseOrderForm
                isEdit={view === 'edit'}
                orderId={view === 'edit' ? selectedId : null}
                vendors={vendors}
                currencies={currencies}
                warehouses={warehouses}
                products={products}
                onBack={() => { setView(selectedId ? 'detail' : 'list'); setError(null); }}
                onSave={async (data) => {
                    let ok;
                    if (view === 'edit') {
                        ok = await update(selectedId, data);
                    } else {
                        ok = await store(data);
                    }
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

    if (view === 'detail' && detail) {
        return (
            <PurchaseOrderDetail
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

    return (
        <div className="space-y-6">
            {/* Header */}
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Purchase Orders</h1>
                    <p className="text-sm text-gray-500">{total} orders total</p>
                </div>
                <Button onClick={() => { setSelectedId(null); setView('create'); }} className="flex items-center gap-2">
                    <Plus className="h-4 w-4" /> New Purchase Order
                </Button>
            </div>

            {/* Filters */}
            <div className="flex flex-col sm:flex-row gap-3">
                <div className="relative flex-1">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <input
                        type="text"
                        value={search}
                        onChange={(e) => handleSearchChange(e.target.value)}
                        placeholder="Search orders..."
                        className="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                    />
                </div>
                <select
                    value={statusFilter}
                    onChange={(e) => setStatusFilter(e.target.value)}
                    className="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                >
                    {PO_STATUS_OPTIONS.map(o => <option key={o.value} value={o.value}>{o.label}</option>)}
                </select>
            </div>

            {/* Table */}
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
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Order Number</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Date</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Vendor</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">Total</th>
                                    <th className="px-4 py-3 text-center font-medium text-gray-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {orders.map((po) => (
                                    <tr key={po.id} className="hover:bg-gray-50 transition-colors">
                                        <td className="px-4 py-3">
                                            <button
                                                onClick={() => loadDetail(po.id)}
                                                className="font-medium text-indigo-600 hover:text-indigo-800"
                                            >
                                                {po.order_number}
                                            </button>
                                        </td>
                                        <td className="px-4 py-3 text-gray-600">{formatDate(po.order_date)}</td>
                                        <td className="px-4 py-3 text-gray-800">{po.vendor?.name || '-'}</td>
                                        <td className="px-4 py-3">
                                            <StatusBadge status={po.status} label={po.status_label} />
                                        </td>
                                        <td className="px-4 py-3 text-right font-medium">
                                            {formatCurrency(po.total_amount, po.currency?.code)}
                                        </td>
                                        <td className="px-4 py-3">
                                            <div className="flex items-center justify-center gap-1">
                                                <button onClick={() => loadDetail(po.id)} className="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100" title="View">
                                                    <Eye className="h-4 w-4" />
                                                </button>
                                                {po.status === 'draft' && (
                                                    <>
                                                        <button onClick={() => { setSelectedId(po.id); setView('edit'); }} className="p-1.5 rounded-lg text-blue-500 hover:bg-blue-50" title="Edit">
                                                            <Edit2 className="h-4 w-4" />
                                                        </button>
                                                        <button onClick={() => handleDelete(po.id)} className="p-1.5 rounded-lg text-red-500 hover:bg-red-50" title="Delete">
                                                            <Trash2 className="h-4 w-4" />
                                                        </button>
                                                    </>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                                {orders.length === 0 && (
                                    <tr>
                                        <td colSpan={6} className="px-4 py-12 text-center text-gray-400">
                                            No purchase orders found.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                    {/* Pagination */}
                    {lastPage > 1 && (
                        <div className="flex items-center justify-between border-t border-gray-200 px-4 py-3">
                            <p className="text-sm text-gray-500">
                                Page {currentPage} of {lastPage} ({total} total)
                            </p>
                            <div className="flex gap-1">
                                {Array.from({ length: Math.min(lastPage, 7) }, (_, i) => i + 1).map(page => (
                                    <button
                                        key={page}
                                        onClick={() => handlePageChange(page)}
                                        className={`px-3 py-1 rounded-lg text-sm ${
                                            page === currentPage
                                                ? 'bg-indigo-600 text-white'
                                                : 'text-gray-600 hover:bg-gray-100'
                                        }`}
                                    >
                                        {page}
                                    </button>
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            )}

            {/* Confirm Dialog */}
            {confirmAction && (
                <Modal onClose={() => setConfirmAction(null)} title={`Confirm ${confirmAction.action}`}>
                    <p className="text-gray-600 mb-4">
                        Are you sure you want to <strong>{confirmAction.action}</strong> this purchase order?
                    </p>
                    <div className="flex justify-end gap-3">
                        <Button onClick={() => setConfirmAction(null)} className="bg-gray-100 text-gray-700 hover:bg-gray-200">Cancel</Button>
                        <Button onClick={executeAction} disabled={saving}>
                            {saving ? 'Processing...' : 'Confirm'}
                        </Button>
                    </div>
                </Modal>
            )}
        </div>
    );
}

// ─── DETAIL VIEW ─────────────────────────────────────
function PurchaseOrderDetail({ order, onBack, onEdit, onAction, onDelete, loading, reload }) {
    if (loading) return <div className="flex justify-center py-20"><Spinner /></div>;

    const status = order.status;
    const canEdit = status === 'draft';
    const canSubmit = status === 'draft';
    const canApprove = status === 'waiting_approval';
    const canCancel = ['draft', 'waiting_approval', 'approved'].includes(status);
    const canSend = status === 'approved';

    return (
        <div className="space-y-6">
            {/* Header */}
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div className="flex items-center gap-3">
                    <button onClick={onBack} className="p-2 rounded-lg hover:bg-gray-100">
                        <ArrowLeft className="h-5 w-5" />
                    </button>
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">{order.order_number}</h1>
                        <StatusBadge status={order.status} label={order.status_label} />
                    </div>
                </div>
                <div className="flex flex-wrap gap-2">
                    {canEdit && (
                        <Button onClick={onEdit} className="flex items-center gap-2 bg-blue-50 text-blue-700 hover:bg-blue-100">
                            <Edit2 className="h-4 w-4" /> Edit
                        </Button>
                    )}
                    {canSubmit && (
                        <Button onClick={() => onAction('submit', order.id)} className="flex items-center gap-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100">
                            <Send className="h-4 w-4" /> Submit
                        </Button>
                    )}
                    {canApprove && (
                        <Button onClick={() => onAction('approve', order.id)} className="flex items-center gap-2 bg-green-50 text-green-700 hover:bg-green-100">
                            <CheckCircle className="h-4 w-4" /> Approve
                        </Button>
                    )}
                    {canSend && (
                        <Button onClick={() => onAction('send', order.id)} className="flex items-center gap-2 bg-purple-50 text-purple-700 hover:bg-purple-100">
                            <Send className="h-4 w-4" /> Send to Vendor
                        </Button>
                    )}
                    {canCancel && (
                        <Button onClick={() => onAction('cancel', order.id)} className="flex items-center gap-2 bg-red-50 text-red-700 hover:bg-red-100">
                            <XCircle className="h-4 w-4" /> Cancel
                        </Button>
                    )}
                    {canEdit && (
                        <Button onClick={() => onDelete(order.id)} className="flex items-center gap-2 bg-red-50 text-red-700 hover:bg-red-100">
                            <Trash2 className="h-4 w-4" /> Delete
                        </Button>
                    )}
                </div>
            </div>

            {/* Info Grid */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <InfoCard label="Vendor" value={order.vendor?.name || '-'} />
                <InfoCard label="Order Date" value={formatDate(order.order_date)} />
                <InfoCard label="Expected Date" value={formatDate(order.expected_date)} />
                <InfoCard label="Currency" value={order.currency?.code || 'IDR'} />
                <InfoCard label="Warehouse" value={order.warehouse?.name || '-'} />
                <InfoCard label="Created By" value={order.creator?.name || '-'} />
            </div>

            {/* Totals */}
            <div className="bg-white rounded-xl border border-gray-200 p-6">
                <div className="flex justify-end gap-8 text-sm">
                    <div className="text-right">
                        <span className="text-gray-500">Subtotal:</span>
                        <p className="font-medium">{formatCurrency(order.subtotal, order.currency?.code)}</p>
                    </div>
                    <div className="text-right">
                        <span className="text-gray-500">Tax:</span>
                        <p className="font-medium">{formatCurrency(order.tax_amount, order.currency?.code)}</p>
                    </div>
                    <div className="text-right">
                        <span className="text-gray-500">Discount:</span>
                        <p className="font-medium">{formatCurrency(order.discount_amount, order.currency?.code)}</p>
                    </div>
                    <div className="text-right border-l border-gray-200 pl-8">
                        <span className="text-gray-500">Total:</span>
                        <p className="text-lg font-bold text-indigo-600">{formatCurrency(order.total_amount, order.currency?.code)}</p>
                    </div>
                </div>
            </div>

            {/* Lines */}
            <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div className="px-6 py-4 border-b border-gray-200">
                    <h3 className="font-semibold text-gray-900">Order Lines</h3>
                </div>
                <div className="overflow-x-auto">
                    <table className="w-full text-sm">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">#</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Product</th>
                                <th className="px-4 py-3 text-right font-medium text-gray-600">Qty</th>
                                <th className="px-4 py-3 text-right font-medium text-gray-600">Received</th>
                                <th className="px-4 py-3 text-right font-medium text-gray-600">Unit Price</th>
                                <th className="px-4 py-3 text-right font-medium text-gray-600">Tax</th>
                                <th className="px-4 py-3 text-right font-medium text-gray-600">Total</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100">
                            {(order.lines || []).map((line, i) => (
                                <tr key={line.id || i} className="hover:bg-gray-50">
                                    <td className="px-4 py-3 text-gray-500">{line.line_number || i + 1}</td>
                                    <td className="px-4 py-3">
                                        <p className="font-medium">{line.product?.name || line.description}</p>
                                        {line.product?.code && <p className="text-xs text-gray-400">{line.product.code}</p>}
                                    </td>
                                    <td className="px-4 py-3 text-right">{line.quantity} {line.uom?.symbol || ''}</td>
                                    <td className="px-4 py-3 text-right">{line.received_qty || 0}</td>
                                    <td className="px-4 py-3 text-right">{formatCurrency(line.unit_price || line.price)}</td>
                                    <td className="px-4 py-3 text-right">{line.tax_rate || 0}%</td>
                                    <td className="px-4 py-3 text-right font-medium">{formatCurrency(line.total)}</td>
                                </tr>
                            ))}
                            {(order.lines || []).length === 0 && (
                                <tr><td colSpan={7} className="px-4 py-8 text-center text-gray-400">No lines</td></tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

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

// ─── CREATE / EDIT FORM ──────────────────────────────
function PurchaseOrderForm({ isEdit, orderId, vendors, currencies, warehouses, products, onBack, onSave, saving, error }) {
    const [form, setForm] = useState({
        vendor_id: '', company_id: 1, currency_id: '', warehouse_id: '',
        order_date: new Date().toISOString().split('T')[0],
        expected_date: '', notes: '',
        lines: [{ product_id: '', quantity: 1, unit_price: 0, description: '', uom_id: null }],
    });
    const [loadingForm, setLoadingForm] = useState(isEdit);

    useEffect(() => {
        if (isEdit && orderId) {
            purchaseOrdersApi.show(orderId).then(res => {
                const po = res.data.data;
                setForm({
                    vendor_id: po.vendor_id || '',
                    company_id: po.company_id || 1,
                    currency_id: po.currency_id || '',
                    warehouse_id: po.warehouse_id || '',
                    order_date: po.order_date || '',
                    expected_date: po.expected_date || '',
                    notes: po.notes || '',
                    lines: (po.lines || []).map(l => ({
                        product_id: l.product_id,
                        quantity: l.quantity,
                        unit_price: l.unit_price || l.price,
                        description: l.description || '',
                        uom_id: l.uom_id,
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
    const addLine = () => {
        setForm(prev => ({
            ...prev,
            lines: [...prev.lines, { product_id: '', quantity: 1, unit_price: 0, description: '', uom_id: null }],
        }));
    };
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
                <button onClick={onBack} className="p-2 rounded-lg hover:bg-gray-100">
                    <ArrowLeft className="h-5 w-5" />
                </button>
                <h1 className="text-2xl font-bold text-gray-900">
                    {isEdit ? `Edit ${orderId}` : 'New Purchase Order'}
                </h1>
            </div>

            {error && (
                <div className="rounded-lg bg-red-50 p-4 text-red-700 text-sm">
                    <p className="font-medium">{error.message || 'Validation error'}</p>
                    {errorMessages.length > 0 && (
                        <ul className="mt-1 list-disc list-inside">
                            {errorMessages.map((m, i) => <li key={i}>{m}</li>)}
                        </ul>
                    )}
                </div>
            )}

            <form onSubmit={handleSubmit} className="space-y-6">
                {/* Header Fields */}
                <div className="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 className="font-semibold text-gray-900 mb-4">Order Information</h3>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Vendor *</label>
                            <select value={form.vendor_id} onChange={e => updateField('vendor_id', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" required>
                                <option value="">Select Vendor</option>
                                {vendors.map(v => <option key={v.id} value={v.id}>{v.name}</option>)}
                            </select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Currency *</label>
                            <select value={form.currency_id} onChange={e => updateField('currency_id', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" required>
                                <option value="">Select Currency</option>
                                {currencies.map(c => <option key={c.id} value={c.id}>{c.code} - {c.name}</option>)}
                            </select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Warehouse</label>
                            <select value={form.warehouse_id} onChange={e => updateField('warehouse_id', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                <option value="">Select Warehouse</option>
                                {warehouses.map(w => <option key={w.id} value={w.id}>{w.name}</option>)}
                            </select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Order Date *</label>
                            <input type="date" value={form.order_date} onChange={e => updateField('order_date', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" required />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Expected Date</label>
                            <input type="date" value={form.expected_date} onChange={e => updateField('expected_date', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" />
                        </div>
                        <div className="md:col-span-1">
                            <label className="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <input type="text" value={form.notes} onChange={e => updateField('notes', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                placeholder="Optional notes..." />
                        </div>
                    </div>
                </div>

                {/* Lines */}
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 className="font-semibold text-gray-900">Order Lines</h3>
                        <Button type="button" onClick={addLine} className="flex items-center gap-2 text-sm bg-indigo-50 text-indigo-700 hover:bg-indigo-100">
                            <Plus className="h-4 w-4" /> Add Line
                        </Button>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-3 py-3 text-left font-medium text-gray-600 w-8">#</th>
                                    <th className="px-3 py-3 text-left font-medium text-gray-600">Product</th>
                                    <th className="px-3 py-3 text-left font-medium text-gray-600">Description</th>
                                    <th className="px-3 py-3 text-right font-medium text-gray-600 w-24">Qty</th>
                                    <th className="px-3 py-3 text-right font-medium text-gray-600 w-36">Unit Price</th>
                                    <th className="px-3 py-3 text-right font-medium text-gray-600 w-32">Subtotal</th>
                                    <th className="px-3 py-3 w-12"></th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {form.lines.map((line, i) => {
                                    const subtotal = (Number(line.quantity) || 0) * (Number(line.unit_price) || 0);
                                    return (
                                        <tr key={i}>
                                            <td className="px-3 py-2 text-gray-500">{i + 1}</td>
                                            <td className="px-3 py-2">
                                                <select value={line.product_id} onChange={e => {
                                                    const pid = e.target.value;
                                                    const prod = products.find(p => p.id == pid);
                                                    updateLine(i, 'product_id', pid);
                                                    if (prod) {
                                                        updateLine(i, 'description', prod.name);
                                                        updateLine(i, 'unit_price', prod.price || 0);
                                                    }
                                                }}
                                                    className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" required>
                                                    <option value="">Select</option>
                                                    {products.map(p => <option key={p.id} value={p.id}>{p.name} ({p.code})</option>)}
                                                </select>
                                            </td>
                                            <td className="px-3 py-2">
                                                <input type="text" value={line.description} onChange={e => updateLine(i, 'description', e.target.value)}
                                                    className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" />
                                            </td>
                                            <td className="px-3 py-2">
                                                <input type="number" value={line.quantity} onChange={e => updateLine(i, 'quantity', e.target.value)}
                                                    className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm text-right focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                                    min="0.01" step="0.01" required />
                                            </td>
                                            <td className="px-3 py-2">
                                                <input type="number" value={line.unit_price} onChange={e => updateLine(i, 'unit_price', e.target.value)}
                                                    className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm text-right focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                                    min="0" step="1" required />
                                            </td>
                                            <td className="px-3 py-2 text-right font-medium text-gray-700">
                                                {formatCurrency(subtotal)}
                                            </td>
                                            <td className="px-3 py-2">
                                                <button type="button" onClick={() => removeLine(i)}
                                                    className="p-1 rounded text-red-400 hover:text-red-600 hover:bg-red-50"
                                                    disabled={form.lines.length <= 1}>
                                                    <Trash2 className="h-4 w-4" />
                                                </button>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                    <div className="px-6 py-3 bg-gray-50 text-right text-sm">
                        <span className="text-gray-500">Grand Total: </span>
                        <span className="font-bold text-indigo-600 text-lg">
                            {formatCurrency(form.lines.reduce((sum, l) => sum + (Number(l.quantity) || 0) * (Number(l.unit_price) || 0), 0))}
                        </span>
                    </div>
                </div>

                {/* Actions */}
                <div className="flex justify-end gap-3">
                    <Button type="button" onClick={onBack} className="bg-gray-100 text-gray-700 hover:bg-gray-200">
                        Cancel
                    </Button>
                    <Button type="submit" disabled={saving} className="flex items-center gap-2">
                        {saving ? 'Saving...' : (isEdit ? 'Update Order' : 'Create Order')}
                    </Button>
                </div>
            </form>
        </div>
    );
}
