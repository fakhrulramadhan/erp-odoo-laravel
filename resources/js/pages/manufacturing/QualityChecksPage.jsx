import { useState, useEffect } from 'react';
import {
    Plus, Search, Eye, Edit2, Trash2, ArrowLeft,
    ClipboardCheck, Play, CheckCircle, XCircle
} from 'lucide-react';
import { qualityChecksApi, manufacturingOrdersApi, masterDataApi } from '../../api/endpoints';
import { useCrudApi } from '../../hooks/useCrudApi';
import { StatusBadge, formatDate } from '../../components/ui/StatusBadge';
import { Button } from '../../components/ui/Button';
import { Modal } from '../../components/ui/Modal';
import { Spinner } from '../../components/ui/Loader';

const QC_STATUS_OPTIONS = [
    { value: '', label: 'All Status' },
    { value: 'draft', label: 'Draft' },
    { value: 'in_progress', label: 'In Progress' },
    { value: 'passed', label: 'Passed' },
    { value: 'failed', label: 'Failed' },
    { value: 'cancelled', label: 'Cancelled' },
];

export default function QualityChecksPage() {
    const {
        data: checks, loading, saving, error, setError,
        currentPage, lastPage, total,
        search, filterValues,
        fetchData, handlePageChange, handleSearchChange, handleFilterChange,
        store, update, destroy, performAction,
    } = useCrudApi(qualityChecksApi);

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
        try {
            const res = await qualityChecksApi.show(id);
            setDetail(res.data.data);
            setSelectedId(id);
            setView('detail');
        } catch {}
    };

    const handleAction = (action, id) => setConfirmAction({ action, id });
    const executeAction = async () => {
        if (!confirmAction) return;
        const { action, id } = confirmAction;
        const map = { startInspection: qualityChecksApi.startInspection, pass: qualityChecksApi.pass, fail: qualityChecksApi.fail };
        const result = await performAction(map[action], id);
        if (result && view === 'detail' && id === selectedId) loadDetail(id);
        setConfirmAction(null);
    };

    const handleDelete = async (id) => {
        if (window.confirm('Delete this quality check?')) {
            const ok = await destroy(id);
            if (ok && view === 'detail') setView('list');
        }
    };

    const statusFilter = filterValues.status || '';

    if (view === 'create' || view === 'edit') {
        return <QualityCheckForm isEdit={view === 'edit'} itemId={selectedId}
            manufacturingOrders={manufacturingOrders} products={products}
            onBack={() => { setView(selectedId ? 'detail' : 'list'); setError(null); }}
            onSave={async (data) => {
                let ok = view === 'edit' ? await update(selectedId, data) : await store(data);
                if (ok) { view === 'edit' ? loadDetail(selectedId) : setView('list'); }
                return ok;
            }}
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
                            <h1 className="text-2xl font-bold text-gray-900">{detail.check_number}</h1>
                            <StatusBadge status={detail.status} label={detail.status_label} />
                        </div>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        {s === 'draft' && <Button onClick={() => { setSelectedId(detail.id); setView('edit'); }} className="flex items-center gap-2 bg-blue-50 text-blue-700 hover:bg-blue-100"><Edit2 className="h-4 w-4" /> Edit</Button>}
                        {s === 'draft' && <Button onClick={() => handleAction('startInspection', detail.id)} className="flex items-center gap-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100"><Play className="h-4 w-4" /> Start Inspection</Button>}
                        {s === 'in_progress' && <Button onClick={() => handleAction('pass', detail.id)} className="flex items-center gap-2 bg-green-50 text-green-700 hover:bg-green-100"><CheckCircle className="h-4 w-4" /> Pass</Button>}
                        {s === 'in_progress' && <Button onClick={() => handleAction('fail', detail.id)} className="flex items-center gap-2 bg-red-50 text-red-700 hover:bg-red-100"><XCircle className="h-4 w-4" /> Fail</Button>}
                        {s === 'draft' && <Button onClick={() => handleDelete(detail.id)} className="flex items-center gap-2 bg-red-50 text-red-700 hover:bg-red-100"><Trash2 className="h-4 w-4" /> Delete</Button>}
                    </div>
                </div>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <InfoCard label="Manufacturing Order" value={detail.manufacturing_order?.order_number || '-'} />
                    <InfoCard label="Product" value={detail.product?.name || '-'} />
                    <InfoCard label="Inspected Qty" value={detail.inspected_quantity || '-'} />
                    <InfoCard label="Passed Qty" value={detail.passed_quantity || 0} />
                    <InfoCard label="Failed Qty" value={detail.failed_quantity || 0} />
                    <InfoCard label="Inspection Date" value={formatDate(detail.inspection_date)} />
                    <InfoCard label="Inspector" value={detail.inspector?.name || '-'} />
                    <InfoCard label="Notes" value={detail.notes || '-'} />
                </div>
                {detail.lines && detail.lines.length > 0 && (
                    <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <div className="px-6 py-4 border-b border-gray-200"><h3 className="font-semibold text-gray-900">Check Lines</h3></div>
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead className="bg-gray-50"><tr>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Control Point</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Result</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Notes</th>
                                </tr></thead>
                                <tbody className="divide-y divide-gray-100">
                                    {detail.lines.map((l) => (
                                        <tr key={l.id} className="hover:bg-gray-50">
                                            <td className="px-4 py-3 font-medium">{l.control_point?.name || '-'}</td>
                                            <td className="px-4 py-3"><StatusBadge status={l.result} /></td>
                                            <td className="px-4 py-3 text-gray-600">{l.notes || '-'}</td>
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
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Quality Checks</h1>
                    <p className="text-sm text-gray-500">{total} checks total</p>
                </div>
                <Button onClick={() => { setSelectedId(null); setView('create'); }} className="flex items-center gap-2"><Plus className="h-4 w-4" /> New Quality Check</Button>
            </div>
            <div className="flex flex-col sm:flex-row gap-3">
                <div className="relative flex-1">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <input type="text" value={search} onChange={(e) => handleSearchChange(e.target.value)} placeholder="Search..." className="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" />
                </div>
                <select value={statusFilter} onChange={(e) => handleFilterChange({ ...filterValues, status: e.target.value })} className="rounded-lg border border-gray-300 px-3 py-2 text-sm">{QC_STATUS_OPTIONS.map(o => <option key={o.value} value={o.value}>{o.label}</option>)}</select>
            </div>
            {loading ? <div className="flex justify-center py-20"><Spinner /></div> : (
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 border-b border-gray-200"><tr>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Check Number</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">MO</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Product</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Date</th>
                                <th className="px-4 py-3 text-center font-medium text-gray-600">Actions</th>
                            </tr></thead>
                            <tbody className="divide-y divide-gray-100">
                                {checks.map((qc) => (
                                    <tr key={qc.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3"><button onClick={() => loadDetail(qc.id)} className="font-medium text-indigo-600 hover:text-indigo-800">{qc.check_number}</button></td>
                                        <td className="px-4 py-3 text-gray-600">{qc.manufacturing_order?.order_number || '-'}</td>
                                        <td className="px-4 py-3 text-gray-800">{qc.product?.name || '-'}</td>
                                        <td className="px-4 py-3"><StatusBadge status={qc.status} label={qc.status_label} /></td>
                                        <td className="px-4 py-3 text-gray-600">{formatDate(qc.inspection_date || qc.created_at)}</td>
                                        <td className="px-4 py-3"><div className="flex items-center justify-center gap-1">
                                            <button onClick={() => loadDetail(qc.id)} className="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100"><Eye className="h-4 w-4" /></button>
                                            {qc.status === 'draft' && <button onClick={() => { setSelectedId(qc.id); setView('edit'); }} className="p-1.5 rounded-lg text-blue-500 hover:bg-blue-50"><Edit2 className="h-4 w-4" /></button>}
                                        </div></td>
                                    </tr>
                                ))}
                                {checks.length === 0 && <tr><td colSpan={6} className="px-4 py-12 text-center text-gray-400">No quality checks found.</td></tr>}
                            </tbody>
                        </table>
                    </div>
                </div>
            )}
            {confirmAction && (
                <Modal onClose={() => setConfirmAction(null)} title={`Confirm ${confirmAction.action}`}>
                    <p className="text-gray-600 mb-4">Are you sure you want to <strong>{confirmAction.action.replace(/([A-Z])/g, ' $1').toLowerCase()}</strong>?</p>
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

function QualityCheckForm({ isEdit, itemId, manufacturingOrders, products, onBack, onSave, saving, error }) {
    const [form, setForm] = useState({ manufacturing_order_id: '', product_id: '', inspected_quantity: 1, notes: '', lines: [] });
    const [loadingForm, setLoadingForm] = useState(isEdit);

    useState(() => {
        if (isEdit && itemId) {
            qualityChecksApi.show(itemId).then(res => {
                const d = res.data.data;
                setForm({ manufacturing_order_id: d.manufacturing_order_id || '', product_id: d.product_id || '', inspected_quantity: d.inspected_quantity || 1, notes: d.notes || '', lines: (d.lines || []).map(l => ({ control_point_id: l.control_point_id, result: l.result || 'pass', notes: l.notes || '' })) });
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
                <h1 className="text-2xl font-bold text-gray-900">{isEdit ? 'Edit Quality Check' : 'New Quality Check'}</h1>
            </div>
            {error && <div className="rounded-lg bg-red-50 p-4 text-red-700 text-sm"><p className="font-medium">{error.message}</p>{errorMessages.length > 0 && <ul className="mt-1 list-disc list-inside">{errorMessages.map((m, i) => <li key={i}>{m}</li>)}</ul>}</div>}
            <form onSubmit={async (e) => { e.preventDefault(); await onSave(form); }} className="space-y-6">
                <div className="bg-white rounded-xl border border-gray-200 p-6">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Manufacturing Order *</label><select value={form.manufacturing_order_id} onChange={e => setForm(p => ({ ...p, manufacturing_order_id: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required><option value="">Select MO</option>{manufacturingOrders.map(mo => <option key={mo.id} value={mo.id}>{mo.order_number}</option>)}</select></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Product *</label><select value={form.product_id} onChange={e => setForm(p => ({ ...p, product_id: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required><option value="">Select Product</option>{products.map(p => <option key={p.id} value={p.id}>{p.name}</option>)}</select></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Inspected Quantity *</label><input type="number" min="0.01" step="0.01" value={form.inspected_quantity} onChange={e => setForm(p => ({ ...p, inspected_quantity: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required /></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Notes</label><input type="text" value={form.notes} onChange={e => setForm(p => ({ ...p, notes: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
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
