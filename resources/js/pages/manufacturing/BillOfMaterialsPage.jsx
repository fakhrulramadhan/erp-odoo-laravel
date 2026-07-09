import { useState, useEffect } from 'react';
import {
    Plus, Search, Eye, Edit2, Trash2, CheckCircle, XCircle,
    ClipboardList, ArrowLeft, Copy, Send
} from 'lucide-react';
import { bomsApi, masterDataApi, routingsApi } from '../../api/endpoints';
import { useCrudApi } from '../../hooks/useCrudApi';
import { StatusBadge, formatDate } from '../../components/ui/StatusBadge';
import { Button } from '../../components/ui/Button';
import { Modal } from '../../components/ui/Modal';
import { Spinner } from '../../components/ui/Loader';

const BOM_STATUS_OPTIONS = [
    { value: '', label: 'All Status' },
    { value: 'draft', label: 'Draft' },
    { value: 'active', label: 'Active' },
    { value: 'under_review', label: 'Under Review' },
    { value: 'approved', label: 'Approved' },
    { value: 'obsolete', label: 'Obsolete' },
    { value: 'cancelled', label: 'Cancelled' },
];

export default function BillOfMaterialsPage() {
    const {
        data: boms, loading, saving, error, setError,
        currentPage, lastPage, total,
        search, filterValues,
        fetchData, handlePageChange, handleSearchChange, handleFilterChange,
        store, update, destroy, performAction,
    } = useCrudApi(bomsApi);

    const [view, setView] = useState('list');
    const [selectedId, setSelectedId] = useState(null);
    const [detail, setDetail] = useState(null);
    const [detailLoading, setDetailLoading] = useState(false);
    const [confirmAction, setConfirmAction] = useState(null);

    const [products, setProducts] = useState([]);
    const [routings, setRoutings] = useState([]);

    useEffect(() => {
        Promise.all([
            masterDataApi.list('products', { per_page: 999 }),
            routingsApi.list({ per_page: 999 }),
        ]).then(([p, r]) => {
            const ex = (res) => { const d = res.data?.data; return Array.isArray(d) ? d : (d?.data || []); };
            setProducts(ex(p));
            setRoutings(ex(r));
        }).catch(() => {});
    }, []);

    const loadDetail = async (id) => {
        setDetailLoading(true);
        try {
            const res = await bomsApi.show(id);
            setDetail(res.data.data);
            setSelectedId(id);
            setView('detail');
        } catch {} finally { setDetailLoading(false); }
    };

    const handleAction = (action, id) => setConfirmAction({ action, id });

    const executeAction = async () => {
        if (!confirmAction) return;
        const { action, id } = confirmAction;
        const map = { approve: bomsApi.approve, activate: bomsApi.activate, cancel: bomsApi.cancel, clone: bomsApi.clone };
        const result = await performAction(map[action], id);
        if (result && view === 'detail' && id === selectedId) loadDetail(id);
        setConfirmAction(null);
    };

    const handleDelete = async (id) => {
        if (window.confirm('Delete this BOM?')) {
            const ok = await destroy(id);
            if (ok && view === 'detail') setView('list');
        }
    };

    const statusFilter = filterValues.status || '';

    if (view === 'create' || view === 'edit') {
        return (
            <BomForm isEdit={view === 'edit'} bomId={view === 'edit' ? selectedId : null}
                products={products} routings={routings}
                onBack={() => { setView(selectedId ? 'detail' : 'list'); setError(null); }}
                onSave={async (data) => {
                    let ok = view === 'edit' ? await update(selectedId, data) : await store(data);
                    if (ok) { view === 'edit' ? loadDetail(selectedId) : setView('list'); }
                    return ok;
                }}
                saving={saving} error={error}
            />
        );
    }

    if (view === 'detail' && detail) {
        const s = detail.status;
        return (
            <div className="space-y-6">
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div className="flex items-center gap-3">
                        <button onClick={() => { setView('list'); setDetail(null); }} className="p-2 rounded-lg hover:bg-gray-100"><ArrowLeft className="h-5 w-5" /></button>
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">{detail.name}</h1>
                            <p className="text-sm text-gray-500">{detail.bom_number}</p>
                            <StatusBadge status={detail.status} label={detail.status_label} />
                        </div>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        {s === 'draft' && <Button onClick={() => { setSelectedId(detail.id); setView('edit'); }} className="flex items-center gap-2 bg-blue-50 text-blue-700 hover:bg-blue-100"><Edit2 className="h-4 w-4" /> Edit</Button>}
                        {s === 'draft' && <Button onClick={() => handleAction('approve', detail.id)} className="flex items-center gap-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100"><Send className="h-4 w-4" /> Approve</Button>}
                        {s === 'approved' && <Button onClick={() => handleAction('activate', detail.id)} className="flex items-center gap-2 bg-green-50 text-green-700 hover:bg-green-100"><CheckCircle className="h-4 w-4" /> Activate</Button>}
                        <Button onClick={() => handleAction('clone', detail.id)} className="flex items-center gap-2 bg-gray-50 text-gray-700 hover:bg-gray-100"><Copy className="h-4 w-4" /> Clone</Button>
                        {['draft', 'under_review'].includes(s) && <Button onClick={() => handleAction('cancel', detail.id)} className="flex items-center gap-2 bg-red-50 text-red-700 hover:bg-red-100"><XCircle className="h-4 w-4" /> Cancel</Button>}
                        {s === 'draft' && <Button onClick={() => handleDelete(detail.id)} className="flex items-center gap-2 bg-red-50 text-red-700 hover:bg-red-100"><Trash2 className="h-4 w-4" /> Delete</Button>}
                    </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <InfoCard label="Product" value={detail.product?.name || '-'} />
                    <InfoCard label="Routing" value={detail.routing?.name || '-'} />
                    <InfoCard label="Quantity" value={detail.quantity} />
                    <InfoCard label="Version" value={detail.version || '1.0'} />
                    <InfoCard label="Effective Date" value={formatDate(detail.effective_date)} />
                    <InfoCard label="Created By" value={detail.creator?.name || '-'} />
                </div>

                {/* BOM Lines */}
                {detail.lines && detail.lines.length > 0 && (
                    <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <div className="px-6 py-4 border-b border-gray-200">
                            <h3 className="font-semibold text-gray-900">Components</h3>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-4 py-3 text-left font-medium text-gray-600">Product</th>
                                        <th className="px-4 py-3 text-right font-medium text-gray-600">Quantity</th>
                                        <th className="px-4 py-3 text-right font-medium text-gray-600">Unit Cost</th>
                                        <th className="px-4 py-3 text-left font-medium text-gray-600">Notes</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100">
                                    {detail.lines.map((l) => (
                                        <tr key={l.id} className="hover:bg-gray-50">
                                            <td className="px-4 py-3 font-medium">{l.product?.name || '-'}</td>
                                            <td className="px-4 py-3 text-right">{l.quantity}</td>
                                            <td className="px-4 py-3 text-right">{l.unit_cost || 0}</td>
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
                    <h1 className="text-2xl font-bold text-gray-900">Bill of Materials</h1>
                    <p className="text-sm text-gray-500">{total} BOMs total</p>
                </div>
                <Button onClick={() => { setSelectedId(null); setView('create'); }} className="flex items-center gap-2">
                    <Plus className="h-4 w-4" /> New BOM
                </Button>
            </div>

            <div className="flex flex-col sm:flex-row gap-3">
                <div className="relative flex-1">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <input type="text" value={search} onChange={(e) => handleSearchChange(e.target.value)}
                        placeholder="Search BOMs..."
                        className="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" />
                </div>
                <select value={statusFilter} onChange={(e) => handleFilterChange({ ...filterValues, status: e.target.value })}
                    className="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    {BOM_STATUS_OPTIONS.map(o => <option key={o.value} value={o.value}>{o.label}</option>)}
                </select>
            </div>

            {loading ? <div className="flex justify-center py-20"><Spinner /></div> : (
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">BOM Number</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Name</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Product</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">Qty</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                                    <th className="px-4 py-3 text-center font-medium text-gray-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {boms.map((bom) => (
                                    <tr key={bom.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3">
                                            <button onClick={() => loadDetail(bom.id)} className="font-medium text-indigo-600 hover:text-indigo-800">{bom.bom_number}</button>
                                        </td>
                                        <td className="px-4 py-3 text-gray-800">{bom.name}</td>
                                        <td className="px-4 py-3 text-gray-600">{bom.product?.name || '-'}</td>
                                        <td className="px-4 py-3 text-right">{bom.quantity}</td>
                                        <td className="px-4 py-3"><StatusBadge status={bom.status} label={bom.status_label} /></td>
                                        <td className="px-4 py-3">
                                            <div className="flex items-center justify-center gap-1">
                                                <button onClick={() => loadDetail(bom.id)} className="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100"><Eye className="h-4 w-4" /></button>
                                                {bom.status === 'draft' && (
                                                    <>
                                                        <button onClick={() => { setSelectedId(bom.id); setView('edit'); }} className="p-1.5 rounded-lg text-blue-500 hover:bg-blue-50"><Edit2 className="h-4 w-4" /></button>
                                                        <button onClick={() => handleDelete(bom.id)} className="p-1.5 rounded-lg text-red-500 hover:bg-red-50"><Trash2 className="h-4 w-4" /></button>
                                                    </>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                                {boms.length === 0 && <tr><td colSpan={6} className="px-4 py-12 text-center text-gray-400">No BOMs found.</td></tr>}
                            </tbody>
                        </table>
                    </div>
                    {lastPage > 1 && (
                        <div className="flex items-center justify-between border-t border-gray-200 px-4 py-3">
                            <p className="text-sm text-gray-500">Page {currentPage} of {lastPage}</p>
                            <div className="flex gap-1">
                                {Array.from({ length: Math.min(lastPage, 7) }, (_, i) => i + 1).map(page => (
                                    <button key={page} onClick={() => handlePageChange(page)}
                                        className={`px-3 py-1 rounded-lg text-sm ${page === currentPage ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100'}`}>{page}</button>
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            )}

            {confirmAction && (
                <Modal onClose={() => setConfirmAction(null)} title={`Confirm ${confirmAction.action}`}>
                    <p className="text-gray-600 mb-4">Are you sure you want to <strong>{confirmAction.action}</strong> this BOM?</p>
                    <div className="flex justify-end gap-3">
                        <Button onClick={() => setConfirmAction(null)} className="bg-gray-100 text-gray-700 hover:bg-gray-200">Cancel</Button>
                        <Button onClick={executeAction} disabled={saving}>{saving ? 'Processing...' : 'Confirm'}</Button>
                    </div>
                </Modal>
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

function BomForm({ isEdit, bomId, products, routings, onBack, onSave, saving, error }) {
    const [form, setForm] = useState({
        name: '', product_id: '', routing_id: '', quantity: 1, version: '1.0',
        effective_date: '', notes: '',
        lines: [{ product_id: '', quantity: 1, unit_cost: 0, notes: '' }],
    });
    const [loadingForm, setLoadingForm] = useState(isEdit);

    useEffect(() => {
        if (isEdit && bomId) {
            bomsApi.show(bomId).then(res => {
                const b = res.data.data;
                setForm({
                    name: b.name || '', product_id: b.product_id || '', routing_id: b.routing_id || '',
                    quantity: b.quantity || 1, version: b.version || '1.0',
                    effective_date: b.effective_date || '', notes: b.notes || '',
                    lines: (b.lines || []).map(l => ({ product_id: l.product_id, quantity: l.quantity, unit_cost: l.unit_cost || 0, notes: l.notes || '' })),
                });
                setLoadingForm(false);
            }).catch(() => setLoadingForm(false));
        }
    }, [isEdit, bomId]);

    const uf = (f, v) => setForm(p => ({ ...p, [f]: v }));
    const ul = (i, f, v) => setForm(p => { const l = [...p.lines]; l[i] = { ...l[i], [f]: v }; return { ...p, lines: l }; });

    if (loadingForm) return <div className="flex justify-center py-20"><Spinner /></div>;
    const errorMessages = error?.errors ? Object.values(error.errors).flat() : [];

    return (
        <div className="space-y-6">
            <div className="flex items-center gap-3">
                <button onClick={onBack} className="p-2 rounded-lg hover:bg-gray-100"><ArrowLeft className="h-5 w-5" /></button>
                <h1 className="text-2xl font-bold text-gray-900">{isEdit ? 'Edit BOM' : 'New BOM'}</h1>
            </div>
            {error && <div className="rounded-lg bg-red-50 p-4 text-red-700 text-sm"><p className="font-medium">{error.message}</p>{errorMessages.length > 0 && <ul className="mt-1 list-disc list-inside">{errorMessages.map((m, i) => <li key={i}>{m}</li>)}</ul>}</div>}
            <form onSubmit={async (e) => { e.preventDefault(); await onSave(form); }} className="space-y-6">
                <div className="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 className="font-semibold text-gray-900 mb-4">BOM Information</h3>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                            <input type="text" value={form.name} onChange={e => uf('name', e.target.value)} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" required />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Product *</label>
                            <select value={form.product_id} onChange={e => uf('product_id', e.target.value)} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                                <option value="">Select Product</option>
                                {products.map(p => <option key={p.id} value={p.id}>{p.name}</option>)}
                            </select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Routing</label>
                            <select value={form.routing_id} onChange={e => uf('routing_id', e.target.value)} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Select Routing</option>
                                {routings.map(r => <option key={r.id} value={r.id}>{r.name}</option>)}
                            </select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Quantity *</label>
                            <input type="number" min="0.01" step="0.01" value={form.quantity} onChange={e => uf('quantity', e.target.value)} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Version</label>
                            <input type="text" value={form.version} onChange={e => uf('version', e.target.value)} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Effective Date</label>
                            <input type="date" value={form.effective_date} onChange={e => uf('effective_date', e.target.value)} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                        </div>
                    </div>
                </div>

                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 className="font-semibold text-gray-900">Components</h3>
                        <Button type="button" onClick={() => setForm(p => ({ ...p, lines: [...p.lines, { product_id: '', quantity: 1, unit_cost: 0, notes: '' }] }))} className="flex items-center gap-2 text-sm bg-indigo-50 text-indigo-700 hover:bg-indigo-100"><Plus className="h-4 w-4" /> Add</Button>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Product *</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">Qty *</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">Unit Cost</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Notes</th>
                                    <th className="px-4 py-3 w-12"></th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {form.lines.map((l, i) => (
                                    <tr key={i}>
                                        <td className="px-4 py-2"><select value={l.product_id} onChange={e => ul(i, 'product_id', e.target.value)} className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" required><option value="">Select</option>{products.map(p => <option key={p.id} value={p.id}>{p.name}</option>)}</select></td>
                                        <td className="px-4 py-2"><input type="number" min="0.01" step="0.01" value={l.quantity} onChange={e => ul(i, 'quantity', e.target.value)} className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm text-right" required /></td>
                                        <td className="px-4 py-2"><input type="number" min="0" step="0.01" value={l.unit_cost} onChange={e => ul(i, 'unit_cost', e.target.value)} className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm text-right" /></td>
                                        <td className="px-4 py-2"><input type="text" value={l.notes} onChange={e => ul(i, 'notes', e.target.value)} className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" /></td>
                                        <td className="px-4 py-2"><button type="button" onClick={() => { if (form.lines.length > 1) setForm(p => ({ ...p, lines: p.lines.filter((_, j) => j !== i) })); }} className="p-1 rounded text-red-500 hover:bg-red-50"><Trash2 className="h-4 w-4" /></button></td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>

                <div className="flex justify-end gap-3">
                    <Button type="button" onClick={onBack} className="bg-gray-100 text-gray-700 hover:bg-gray-200">Cancel</Button>
                    <Button type="submit" disabled={saving}>{saving ? 'Saving...' : 'Save BOM'}</Button>
                </div>
            </form>
        </div>
    );
}
