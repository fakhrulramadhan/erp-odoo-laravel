import { useState, useEffect } from 'react';
import { Plus, Search, Eye, Trash2, CheckCircle, ArrowLeft, ClipboardList } from 'lucide-react';
import { inventoryAdjustmentsApi, inventoryApi, masterDataApi } from '../../api/endpoints';
import { useCrudApi } from '../../hooks/useCrudApi';
import { StatusBadge, formatDate } from '../../components/ui/StatusBadge';
import { Button } from '../../components/ui/Button';
import { Modal } from '../../components/ui/Modal';
import { Spinner } from '../../components/ui/Loader';

export default function InventoryAdjustmentsPage() {
    const {
        data: adjustments, loading, saving, error, setError,
        currentPage, lastPage, total,
        search,
        fetchData, handlePageChange, handleSearchChange,
        store, destroy, performAction,
    } = useCrudApi(inventoryAdjustmentsApi);

    const [view, setView] = useState('list');
    const [selectedId, setSelectedId] = useState(null);
    const [detail, setDetail] = useState(null);
    const [detailLoading, setDetailLoading] = useState(false);
    const [confirmAction, setConfirmAction] = useState(null);
    const [warehouses, setWarehouses] = useState([]);
    const [products, setProducts] = useState([]);
    const [locations, setLocations] = useState([]);

    useEffect(() => {
        async function loadLookups() {
            try {
                const [w, p, l] = await Promise.all([
                    masterDataApi.list('warehouses', { per_page: 999 }),
                    masterDataApi.list('products', { per_page: 999 }),
                    masterDataApi.list('stock-locations', { per_page: 999 }),
                ]);
                const extract = (res) => { const d = res.data?.data; return Array.isArray(d) ? d : (d?.data || []); };
                setWarehouses(extract(w));
                setProducts(extract(p));
                setLocations(extract(l));
            } catch {}
        }
        loadLookups();
    }, []);

    const loadDetail = async (id) => {
        setDetailLoading(true);
        try {
            const res = await inventoryAdjustmentsApi.show(id);
            setDetail(res.data.data);
            setSelectedId(id);
            setView('detail');
        } catch {} finally { setDetailLoading(false); }
    };

    const handleValidate = async (id) => setConfirmAction({ action: 'validate', id });
    const executeAction = async () => {
        if (!confirmAction) return;
        const result = await performAction(inventoryAdjustmentsApi.validate, confirmAction.id);
        if (result && view === 'detail') loadDetail(confirmAction.id);
        setConfirmAction(null);
    };

    const handleDelete = async (id) => {
        if (window.confirm('Delete this adjustment?')) {
            const ok = await destroy(id);
            if (ok && view === 'detail') setView('list');
        }
    };

    // ─── Form ──────────────────────────────────────
    const [form, setForm] = useState({
        warehouse_id: '', adjustment_date: new Date().toISOString().split('T')[0],
        reason: '', notes: '',
        lines: [{ product_id: '', location_id: '', theoretical_qty: 0, actual_qty: 0 }],
    });

    const resetForm = () => {
        setForm({ warehouse_id: '', adjustment_date: new Date().toISOString().split('T')[0], reason: '', notes: '', lines: [{ product_id: '', location_id: '', theoretical_qty: 0, actual_qty: 0 }] });
    };

    const updateField = (f, v) => setForm(p => ({ ...p, [f]: v }));
    const updateLine = (i, f, v) => { setForm(p => { const l = [...p.lines]; l[i] = { ...l[i], [f]: v }; return { ...p, lines: l }; }); };
    const addLine = () => setForm(p => ({ ...p, lines: [...p.lines, { product_id: '', location_id: '', theoretical_qty: 0, actual_qty: 0 }] }));
    const removeLine = (i) => { if (form.lines.length <= 1) return; setForm(p => ({ ...p, lines: p.lines.filter((_, idx) => idx !== i) })); };

    if (view === 'create') {
        const errorMessages = error?.errors ? Object.values(error.errors).flat() : [];
        return (
            <div className="space-y-6">
                <div className="flex items-center gap-3">
                    <button onClick={() => { setView('list'); resetForm(); setError(null); }} className="p-2 rounded-lg hover:bg-gray-100"><ArrowLeft className="h-5 w-5" /></button>
                    <h1 className="text-2xl font-bold text-gray-900">New Inventory Adjustment</h1>
                </div>
                {error && <div className="rounded-lg bg-red-50 p-4 text-red-700 text-sm">
                    <p className="font-medium">{error.message || 'Error'}</p>
                    {errorMessages.length > 0 && <ul className="mt-1 list-disc list-inside">{errorMessages.map((m, i) => <li key={i}>{m}</li>)}</ul>}
                </div>}
                <form onSubmit={async (e) => {
                    e.preventDefault();
                    const ok = await store(form);
                    if (ok) { setView('list'); resetForm(); }
                }} className="space-y-6">
                    <div className="bg-white rounded-xl border border-gray-200 p-6">
                        <h3 className="font-semibold text-gray-900 mb-4">Adjustment Information</h3>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Warehouse *</label>
                                <select value={form.warehouse_id} onChange={e => updateField('warehouse_id', e.target.value)} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                                    <option value="">Select Warehouse</option>
                                    {warehouses.map(w => <option key={w.id} value={w.id}>{w.name}</option>)}
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                                <input type="date" value={form.adjustment_date} onChange={e => updateField('adjustment_date', e.target.value)} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                                <input type="text" value={form.reason} onChange={e => updateField('reason', e.target.value)} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="Stock count, damage, etc." />
                            </div>
                        </div>
                    </div>
                    <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <div className="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                            <h3 className="font-semibold text-gray-900">Adjustment Lines</h3>
                            <Button type="button" onClick={addLine} className="flex items-center gap-2 text-sm bg-indigo-50 text-indigo-700 hover:bg-indigo-100"><Plus className="h-4 w-4" /> Add Line</Button>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead className="bg-gray-50"><tr>
                                    <th className="px-3 py-3 text-left font-medium text-gray-600 w-8">#</th>
                                    <th className="px-3 py-3 text-left font-medium text-gray-600">Product</th>
                                    <th className="px-3 py-3 text-left font-medium text-gray-600">Location</th>
                                    <th className="px-3 py-3 text-right font-medium text-gray-600 w-28">Theoretical</th>
                                    <th className="px-3 py-3 text-right font-medium text-gray-600 w-28">Actual</th>
                                    <th className="px-3 py-3 text-right font-medium text-gray-600 w-28">Difference</th>
                                    <th className="px-3 py-3 w-12"></th>
                                </tr></thead>
                                <tbody className="divide-y divide-gray-100">
                                    {form.lines.map((line, i) => {
                                        const diff = (Number(line.actual_qty) || 0) - (Number(line.theoretical_qty) || 0);
                                        return (
                                            <tr key={i}>
                                                <td className="px-3 py-2 text-gray-500">{i + 1}</td>
                                                <td className="px-3 py-2">
                                                    <select value={line.product_id} onChange={e => updateLine(i, 'product_id', e.target.value)} className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" required>
                                                        <option value="">Select</option>
                                                        {products.map(p => <option key={p.id} value={p.id}>{p.name}</option>)}
                                                    </select>
                                                </td>
                                                <td className="px-3 py-2">
                                                    <select value={line.location_id} onChange={e => updateLine(i, 'location_id', e.target.value)} className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm">
                                                        <option value="">Select</option>
                                                        {locations.map(l => <option key={l.id} value={l.id}>{l.name}</option>)}
                                                    </select>
                                                </td>
                                                <td className="px-3 py-2"><input type="number" value={line.theoretical_qty} onChange={e => updateLine(i, 'theoretical_qty', e.target.value)} className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm text-right" /></td>
                                                <td className="px-3 py-2"><input type="number" value={line.actual_qty} onChange={e => updateLine(i, 'actual_qty', e.target.value)} className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm text-right" /></td>
                                                <td className={`px-3 py-2 text-right font-medium ${diff > 0 ? 'text-green-600' : diff < 0 ? 'text-red-600' : 'text-gray-500'}`}>{diff > 0 ? '+' : ''}{diff}</td>
                                                <td className="px-3 py-2"><button type="button" onClick={() => removeLine(i)} className="p-1 rounded text-red-400 hover:text-red-600" disabled={form.lines.length <= 1}><Trash2 className="h-4 w-4" /></button></td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div className="flex justify-end gap-3">
                        <Button type="button" onClick={() => { setView('list'); resetForm(); setError(null); }} className="bg-gray-100 text-gray-700 hover:bg-gray-200">Cancel</Button>
                        <Button type="submit" disabled={saving}>{saving ? 'Saving...' : 'Create Adjustment'}</Button>
                    </div>
                </form>
            </div>
        );
    }

    if (view === 'detail' && detail) {
        const canValidate = !detail.validated_at;
        return (
            <div className="space-y-6">
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div className="flex items-center gap-3">
                        <button onClick={() => { setView('list'); setDetail(null); }} className="p-2 rounded-lg hover:bg-gray-100"><ArrowLeft className="h-5 w-5" /></button>
                        <div><h1 className="text-2xl font-bold text-gray-900">Adjustment #{detail.id}</h1>
                            {detail.validated_at ? <StatusBadge status="validated" /> : <StatusBadge status="draft" />}
                        </div>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        {canValidate && <Button onClick={() => handleValidate(detail.id)} className="flex items-center gap-2 bg-green-50 text-green-700 hover:bg-green-100"><CheckCircle className="h-4 w-4" /> Validate</Button>}
                        {!detail.validated_at && <Button onClick={() => handleDelete(detail.id)} className="flex items-center gap-2 bg-red-50 text-red-700 hover:bg-red-100"><Trash2 className="h-4 w-4" /> Delete</Button>}
                    </div>
                </div>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <InfoCard label="Warehouse" value={detail.warehouse?.name || '-'} />
                    <InfoCard label="Date" value={formatDate(detail.adjustment_date)} />
                    <InfoCard label="Reason" value={detail.reason || '-'} />
                    <InfoCard label="Validated At" value={detail.validated_at ? formatDate(detail.validated_at) : 'Not validated'} />
                    <InfoCard label="Validated By" value={detail.validator?.name || '-'} />
                </div>
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="px-6 py-4 border-b border-gray-200"><h3 className="font-semibold text-gray-900">Lines</h3></div>
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50"><tr>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">#</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Product</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Location</th>
                                <th className="px-4 py-3 text-right font-medium text-gray-600">Theoretical</th>
                                <th className="px-4 py-3 text-right font-medium text-gray-600">Actual</th>
                                <th className="px-4 py-3 text-right font-medium text-gray-600">Difference</th>
                            </tr></thead>
                            <tbody className="divide-y divide-gray-100">
                                {(detail.lines || []).map((l, i) => {
                                    const diff = (Number(l.actual_qty) || 0) - (Number(l.theoretical_qty) || 0);
                                    return (
                                        <tr key={i} className="hover:bg-gray-50">
                                            <td className="px-4 py-3 text-gray-500">{i + 1}</td>
                                            <td className="px-4 py-3 font-medium">{l.product?.name || `Product #${l.product_id}`}</td>
                                            <td className="px-4 py-3">{l.stock_location?.name || l.location?.name || '-'}</td>
                                            <td className="px-4 py-3 text-right">{l.theoretical_qty || 0}</td>
                                            <td className="px-4 py-3 text-right font-medium">{l.actual_qty || 0}</td>
                                            <td className={`px-4 py-3 text-right font-medium ${diff > 0 ? 'text-green-600' : diff < 0 ? 'text-red-600' : 'text-gray-500'}`}>{diff > 0 ? '+' : ''}{diff}</td>
                                        </tr>
                                    );
                                })}
                                {(detail.lines || []).length === 0 && <tr><td colSpan={6} className="px-4 py-8 text-center text-gray-400">No lines</td></tr>}
                            </tbody>
                        </table>
                    </div>
                </div>
                {confirmAction && <Modal onClose={() => setConfirmAction(null)} title="Validate Adjustment">
                    <p className="text-gray-600 mb-2">This will update stock quantities based on the differences.</p>
                    <p className="text-sm text-orange-600 mb-4">⚠ This action cannot be undone.</p>
                    <div className="flex justify-end gap-3">
                        <Button onClick={() => setConfirmAction(null)} className="bg-gray-100 text-gray-700 hover:bg-gray-200">Cancel</Button>
                        <Button onClick={executeAction} disabled={saving}>{saving ? 'Processing...' : 'Validate'}</Button>
                    </div>
                </Modal>}
            </div>
        );
    }

    // ─── LIST ──────────────────────────────────────
    return (
        <div className="space-y-6">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div><h1 className="text-2xl font-bold text-gray-900">Inventory Adjustments</h1><p className="text-sm text-gray-500">{total} adjustments</p></div>
                <Button onClick={() => { resetForm(); setView('create'); }} className="flex items-center gap-2"><Plus className="h-4 w-4" /> New Adjustment</Button>
            </div>
            <div className="relative max-w-md">
                <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                <input type="text" value={search} onChange={e => handleSearchChange(e.target.value)} placeholder="Search adjustments..." className="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm" />
            </div>
            {loading ? <div className="flex justify-center py-20"><Spinner /></div> : (
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 border-b border-gray-200"><tr>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">ID</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Date</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Warehouse</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Reason</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                                <th className="px-4 py-3 text-center font-medium text-gray-600">Actions</th>
                            </tr></thead>
                            <tbody className="divide-y divide-gray-100">
                                {adjustments.map(a => (
                                    <tr key={a.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3"><button onClick={() => loadDetail(a.id)} className="font-medium text-indigo-600 hover:text-indigo-800">#{a.id}</button></td>
                                        <td className="px-4 py-3 text-gray-600">{formatDate(a.adjustment_date)}</td>
                                        <td className="px-4 py-3">{a.warehouse?.name || '-'}</td>
                                        <td className="px-4 py-3 text-gray-600">{a.reason || '-'}</td>
                                        <td className="px-4 py-3">{a.validated_at ? <StatusBadge status="validated" /> : <StatusBadge status="draft" />}</td>
                                        <td className="px-4 py-3"><div className="flex items-center justify-center gap-1">
                                            <button onClick={() => loadDetail(a.id)} className="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100"><Eye className="h-4 w-4" /></button>
                                            {!a.validated_at && <button onClick={() => handleDelete(a.id)} className="p-1.5 rounded-lg text-red-500 hover:bg-red-50"><Trash2 className="h-4 w-4" /></button>}
                                        </div></td>
                                    </tr>
                                ))}
                                {adjustments.length === 0 && <tr><td colSpan={6} className="px-4 py-12 text-center text-gray-400">No adjustments found.</td></tr>}
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
