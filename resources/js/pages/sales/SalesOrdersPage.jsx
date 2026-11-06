import { useState } from 'react';
import {
    Plus, Search, Eye, Edit2, Trash2, CheckCircle, XCircle,
    ArrowLeft, Truck, FileText
} from 'lucide-react';
import { salesOrdersApi } from '../../api/endpoints';
import { useCrudApi } from '../../hooks/useCrudApi';
import { StatusBadge, formatCurrency, formatDate } from '../../components/ui/StatusBadge';
import { Button } from '../../components/ui/Button';
import { Modal } from '../../components/ui/Modal';
import { Spinner } from '../../components/ui/Loader';

const STATUS_OPTIONS = [
    { value: '', label: 'All Status' },
    { value: 'draft', label: 'Draft' },
    { value: 'confirmed', label: 'Confirmed' },
    { value: 'partially_delivered', label: 'Partially Delivered' },
    { value: 'delivered', label: 'Delivered' },
    { value: 'invoiced', label: 'Invoiced' },
    { value: 'closed', label: 'Closed' },
    { value: 'cancelled', label: 'Cancelled' },
];

const ACTION_LABELS = {
    confirm: 'confirm',
    cancel: 'cancel',
    createDelivery: 'create delivery',
    createInvoice: 'create customer invoice',
};

const ACTION_HINTS = {
    confirm: 'The sales order will be confirmed and can then be shipped.',
    cancel: 'This sales order will be cancelled.',
    createDelivery: 'An outgoing delivery will be created. Validate it in Inventory afterwards to ship the goods.',
    createInvoice: 'A customer invoice will be created from the delivered quantities and posted to Accounts Receivable and Sales Revenue.',
};

export default function SalesOrdersPage() {
    const {
        data: orders, loading, saving, error, setError,
        currentPage, lastPage, total,
        search, filterValues,
        handlePageChange, handleSearchChange, handleFilterChange,
        destroy, performAction,
    } = useCrudApi(salesOrdersApi);

    const [view, setView] = useState('list');
    const [selectedId, setSelectedId] = useState(null);
    const [detail, setDetail] = useState(null);
    const [detailLoading, setDetailLoading] = useState(false);
    const [confirmAction, setConfirmAction] = useState(null);

    const loadDetail = async (id) => {
        setDetailLoading(true);
        try {
            const res = await salesOrdersApi.show(id);
            setDetail(res.data.data);
            setSelectedId(id);
            setView('detail');
        } catch (err) {
            setError(err.response?.data?.message || err.message);
        } finally {
            setDetailLoading(false);
        }
    };

    const executeAction = async () => {
        if (!confirmAction) return;
        const { action, id } = confirmAction;
        const map = {
            confirm: salesOrdersApi.confirm,
            cancel: salesOrdersApi.cancel,
            createDelivery: salesOrdersApi.createDelivery,
            createInvoice: salesOrdersApi.createInvoice,
        };
        const result = await performAction(map[action], id);
        if (!result) return;
        if (view === 'detail') loadDetail(selectedId);
        setConfirmAction(null);
    };

    const handleDelete = async (id) => {
        if (window.confirm('Delete this sales order?')) {
            const ok = await destroy(id);
            if (ok && view === 'detail') setView('list');
        }
    };

    if (view === 'detail' && detail) {
        return (
            <>
                <SalesOrderDetail
                    order={detail}
                    onBack={() => { setView('list'); setDetail(null); }}
                    onAction={(action, id) => setConfirmAction({ action, id })}
                    onDelete={handleDelete}
                    loading={detailLoading}
                    error={error}
                />
                <ConfirmActionModal
                    confirmAction={confirmAction}
                    saving={saving}
                    onClose={() => setConfirmAction(null)}
                    onConfirm={executeAction}
                    error={error}
                />
            </>
        );
    }

    return (
        <div className="space-y-6">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Sales Orders</h1>
                    <p className="text-sm text-gray-500">Confirm, ship and invoice customer orders.</p>
                </div>
            </div>

            {typeof error === 'string' && error && (
                <div className="rounded-lg bg-red-50 px-4 py-2 text-sm text-red-700">{error}</div>
            )}

            <div className="bg-white rounded-xl border border-gray-200 p-4">
                <div className="flex flex-wrap gap-3">
                    <div className="relative flex-1 min-w-[200px]">
                        <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                        <input
                            type="text"
                            value={search}
                            onChange={e => handleSearchChange(e.target.value)}
                            placeholder="Search order number or customer..."
                            className="w-full rounded-lg border border-gray-300 pl-10 pr-3 py-2 text-sm"
                        />
                    </div>
                    <select
                        value={filterValues.status || ''}
                        onChange={e => handleFilterChange({ ...filterValues, status: e.target.value })}
                        className="rounded-lg border border-gray-300 px-3 py-2 text-sm"
                    >
                        {STATUS_OPTIONS.map(o => <option key={o.value} value={o.value}>{o.label}</option>)}
                    </select>
                </div>
            </div>

            <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                {loading ? (
                    <div className="flex justify-center py-20"><Spinner /></div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="min-w-full text-sm">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Order #</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Customer</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Order Date</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">Total</th>
                                    <th className="px-4 py-3 text-center font-medium text-gray-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {orders.length === 0 && (
                                    <tr><td colSpan={6} className="px-4 py-10 text-center text-gray-500">No sales orders yet.</td></tr>
                                )}
                                {orders.map(so => (
                                    <tr key={so.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3 font-medium text-gray-900">{so.order_number}</td>
                                        <td className="px-4 py-3 text-gray-600">{so.customer?.name || '-'}</td>
                                        <td className="px-4 py-3 text-gray-600">{formatDate(so.order_date)}</td>
                                        <td className="px-4 py-3"><StatusBadge status={so.status} label={so.status_label} /></td>
                                        <td className="px-4 py-3 text-right font-medium">{formatCurrency(so.total_amount)}</td>
                                        <td className="px-4 py-3 text-center">
                                            <button onClick={() => loadDetail(so.id)} className="text-xs font-medium text-blue-600 hover:text-blue-800 inline-flex items-center gap-1">
                                                <Eye className="h-3 w-3" /> View
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}

                {lastPage > 1 && (
                    <div className="flex items-center justify-between px-4 py-3 border-t border-gray-200">
                        <p className="text-xs text-gray-500">Page {currentPage} of {lastPage} · {total} orders</p>
                        <div className="flex gap-2">
                            <Button variant="secondary" size="sm" disabled={currentPage <= 1} onClick={() => handlePageChange(currentPage - 1)}>Previous</Button>
                            <Button variant="secondary" size="sm" disabled={currentPage >= lastPage} onClick={() => handlePageChange(currentPage + 1)}>Next</Button>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}

function SalesOrderDetail({ order, onBack, onAction, onDelete, loading, error }) {
    if (loading) return <div className="flex justify-center py-20"><Spinner /></div>;

    const status = order.status;

    const canEdit = status === 'draft';
    const canConfirm = status === 'draft';
    const canCancel = ['draft', 'confirmed'].includes(status);
    const canDeliver = ['confirmed', 'partially_delivered'].includes(status);
    const canInvoice = ['delivered', 'partially_delivered'].includes(status) && !order.invoice;

    return (
        <div className="space-y-6">
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
                {typeof error === 'string' && error && (
                    <div className="rounded-lg bg-red-50 px-4 py-2 text-sm text-red-700">{error}</div>
                )}
                <div className="flex flex-wrap gap-2">
                    {canConfirm && (
                        <Button onClick={() => onAction('confirm', order.id)} className="flex items-center gap-2 bg-green-600 text-white hover:bg-green-700">
                            <CheckCircle className="h-4 w-4" /> Confirm
                        </Button>
                    )}
                    {canDeliver && (
                        <Button onClick={() => onAction('createDelivery', order.id)} className="flex items-center gap-2 bg-purple-600 text-white hover:bg-purple-700">
                            <Truck className="h-4 w-4" /> Create Delivery
                        </Button>
                    )}
                    {canInvoice && (
                        <Button onClick={() => onAction('createInvoice', order.id)} className="flex items-center gap-2 bg-indigo-600 text-white hover:bg-indigo-700">
                            <FileText className="h-4 w-4" /> Create Invoice
                        </Button>
                    )}
                    {canCancel && (
                        <Button onClick={() => onAction('cancel', order.id)} className="flex items-center gap-2 bg-red-600 text-white hover:bg-red-700">
                            <XCircle className="h-4 w-4" /> Cancel
                        </Button>
                    )}
                    {canEdit && (
                        <Button onClick={() => onDelete(order.id)} className="flex items-center gap-2 bg-red-600 text-white hover:bg-red-700">
                            <Trash2 className="h-4 w-4" /> Delete
                        </Button>
                    )}
                </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <InfoCard label="Customer" value={order.customer?.name || '-'} />
                <InfoCard label="Order Date" value={formatDate(order.order_date)} />
                <InfoCard label="Delivery Date" value={formatDate(order.delivery_date)} />
                <InfoCard label="Currency" value={order.currency?.code || 'IDR'} />
                <InfoCard label="Warehouse" value={order.warehouse?.name || '-'} />
                <InfoCard label="Created By" value={order.creator?.name || '-'} />
            </div>

            <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div className="px-6 py-4 border-b border-gray-200"><h3 className="font-semibold text-gray-900">Lines</h3></div>
                <div className="overflow-x-auto">
                    <table className="min-w-full text-sm">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Product</th>
                                <th className="px-4 py-3 text-right font-medium text-gray-600">Qty</th>
                                <th className="px-4 py-3 text-right font-medium text-gray-600">Delivered</th>
                                <th className="px-4 py-3 text-right font-medium text-gray-600">Unit Price</th>
                                <th className="px-4 py-3 text-right font-medium text-gray-600">Total</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100">
                            {(order.lines || []).map(line => (
                                <tr key={line.id}>
                                    <td className="px-4 py-3 text-gray-900">{line.product?.name || line.description || '-'}</td>
                                    <td className="px-4 py-3 text-right">{line.quantity}</td>
                                    <td className="px-4 py-3 text-right">{line.delivered_qty}</td>
                                    <td className="px-4 py-3 text-right">{formatCurrency(line.unit_price)}</td>
                                    <td className="px-4 py-3 text-right font-medium">{formatCurrency(line.total)}</td>
                                </tr>
                            ))}
                        </tbody>
                        <tfoot className="bg-gray-50">
                            <tr>
                                <td colSpan={4} className="px-4 py-3 text-right font-medium text-gray-600">Total</td>
                                <td className="px-4 py-3 text-right font-bold text-gray-900">{formatCurrency(order.total_amount)}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {order.invoice && (
                <div className="bg-white rounded-xl border border-gray-200 p-4">
                    <p className="text-sm text-gray-600">
                        Customer invoice <span className="font-medium text-gray-900">{order.invoice.invoice_number}</span>
                        {' '}— {formatCurrency(order.invoice.total_amount)} · {order.invoice.status}
                    </p>
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

function ConfirmActionModal({ confirmAction, saving, onClose, onConfirm, error }) {
    if (!confirmAction) return null;

    return (
        <Modal onClose={onClose} title={`Confirm ${ACTION_LABELS[confirmAction.action] || confirmAction.action}`}>
            <p className="text-gray-600 mb-4">
                {ACTION_HINTS[confirmAction.action] || `Are you sure you want to ${confirmAction.action} this sales order?`}
            </p>
            {typeof error === 'string' && error && (
                <p className="mb-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{error}</p>
            )}
            <div className="flex justify-end gap-3">
                <Button onClick={onClose} className="bg-gray-100 text-gray-700 hover:bg-gray-200">Cancel</Button>
                <Button onClick={onConfirm} disabled={saving}>
                    {saving ? 'Processing...' : 'Confirm'}
                </Button>
            </div>
        </Modal>
    );
}
