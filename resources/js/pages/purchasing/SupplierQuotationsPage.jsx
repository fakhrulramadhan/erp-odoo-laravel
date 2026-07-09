import { useState, useEffect } from 'react';
import { Plus, Search, Eye, Edit2, Trash2, Send, CheckCircle, XCircle, ArrowLeft, FileText } from 'lucide-react';
import { supplierQuotationsApi, masterDataApi, settingsApi } from '../../api/endpoints';
import { useCrudApi } from '../../hooks/useCrudApi';
import { StatusBadge, formatCurrency, formatDate } from '../../components/ui/StatusBadge';
import { Button } from '../../components/ui/Button';
import { Modal } from '../../components/ui/Modal';
import { Spinner } from '../../components/ui/Loader';

const STATUS_OPTIONS = [
    { value: '', label: 'All Status' },
    { value: 'draft', label: 'Draft' },
    { value: 'sent', label: 'Sent' },
    { value: 'received', label: 'Received' },
    { value: 'accepted', label: 'Accepted' },
    { value: 'rejected', label: 'Rejected' },
    { value: 'cancelled', label: 'Cancelled' },
];

export default function SupplierQuotationsPage() {
    const {
        data: quotations, loading, saving, error, setError,
        currentPage, lastPage, total,
        search, filterValues,
        fetchData, handlePageChange, handleSearchChange, handleFilterChange,
        store, update, destroy, performAction,
    } = useCrudApi(supplierQuotationsApi);

    const [view, setView] = useState('list');
    const [selectedId, setSelectedId] = useState(null);
    const [detail, setDetail] = useState(null);
    const [detailLoading, setDetailLoading] = useState(false);
    const [confirmAction, setConfirmAction] = useState(null);
    const [vendors, setVendors] = useState([]);
    const [currencies, setCurrencies] = useState([]);
    const [products, setProducts] = useState([]);

    useEffect(() => {
        async function loadLookups() {
            try {
                const [v, c, p] = await Promise.all([
                    masterDataApi.list('vendors', { per_page: 999 }),
                    settingsApi.currencies({ per_page: 999 }),
                    masterDataApi.list('products', { per_page: 999 }),
                ]);
                const extract = (res) => { const d = res.data?.data; return Array.isArray(d) ? d : (d?.data || []); };
                setVendors(extract(v));
                setCurrencies(extract(c));
                setProducts(extract(p));
            } catch {}
        }
        loadLookups();
    }, []);

    const loadDetail = async (id) => {
        setDetailLoading(true);
        try {
            const res = await supplierQuotationsApi.show(id);
            setDetail(res.data.data);
            setSelectedId(id);
            setView('detail');
        } catch {} finally { setDetailLoading(false); }
    };

    const handleAction = (action, id) => setConfirmAction({ action, id });
    const executeAction = async () => {
        if (!confirmAction) return;
        const { action, id } = confirmAction;
        const map = { send: supplierQuotationsApi.sendToVendor, accept: supplierQuotationsApi.accept, reject: supplierQuotationsApi.reject };
        const result = await performAction(map[action], id);
        if (result && view === 'detail') loadDetail(id);
        setConfirmAction(null);
    };

    const handleDelete = async (id) => {
        if (window.confirm('Delete this quotation?')) {
            const ok = await destroy(id);
            if (ok && view === 'detail') setView('list');
        }
    };

    const statusFilter = filterValues.status || '';

    // ─── Form state ────────────────────────────────
    const [form, setForm] = useState({
        vendor_id: '', company_id: 1, currency_id: '',
        quotation_date: new Date().toISOString().split('T')[0],
        validity_date: '', notes: '',
        lines: [{ product_id: '', quantity: 1, price: 0, description: '' }],
    });
    const [loadingForm, setLoadingForm] = useState(false);

    const initForm = async (id) => {
        setLoadingForm(true);
        try {
            const res = await supplierQuotationsApi.show(id);
            const sq = res.data.data;
            setForm({
                vendor_id: sq.vendor_id || '', company_id: sq.company_id || 1, currency_id: sq.currency_id || '',
                quotation_date: sq.quotation_date || '', validity_date: sq.validity_date || '', notes: sq.notes || '',
                lines: (sq.lines || []).map(l => ({ product_id: l.product_id, quantity: l.quantity, price: l.price, description: l.description || '' })),
            });
        } catch {} finally { setLoadingForm(false); }
    };

    const updateField = (f, v) => setForm(p => ({ ...p, [f]: v }));
    const updateLine = (i, f, v) => {
        setForm(p => { const l = [...p.lines]; l[i] = { ...l[i], [f]: v }; return { ...p, lines: l }; });
    };
    const addLine = () => setForm(p => ({ ...p, lines: [...p.lines, { product_id: '', quantity: 1, price: 0, description: '' }] }));
    const removeLine = (i) => { if (form.lines.length <= 1) return; setForm(p => ({ ...p, lines: p.lines.filter((_, idx) => idx !== i) })); };

    if (view === 'create' || view === 'edit') {
        const isEdit = view === 'edit';
        const errorMessages = error?.errors ? Object.values(error.errors).flat() : [];
        return (
            <div className="space-y-6">
                <div className="flex items-center gap-3">
                    <button onClick={() => { setView(selectedId ? 'detail' : 'list'); setError(null); }} className="p-2 rounded-lg hover:bg-gray-100"><ArrowLeft className="h-5 w-5" /></button>
                    <h1 className="text-2xl font-bold text-gray-900">{isEdit ? 'Edit Quotation' : 'New Quotation'}</h1>
                </div>
                {error && <div className="rounded-lg bg-red-50 p-4 text-red-700 text-sm">
                    <p className="font-medium">{error.message || 'Error'}</p>
                    {errorMessages.length > 0 && <ul className="mt-1 list-disc list-inside">{errorMessages.map((m, i) => <li key={i}>{m}</li>)}</ul>}
                </div>}
                <form onSubmit={async (e) => {
                    e.preventDefault();
                    let ok;
                    if (isEdit) { ok = await update(selectedId, form); if (ok) loadDetail(selectedId); }
                    else { ok = await store(form); if (ok) setView('list'); }
                }} className="space-y-6">
                    <div className="bg-white rounded-xl border border-gray-200 p-6">
                        <h3 className="font-semibold text-gray-900 mb-4">Quotation Information</h3>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Vendor *</label>
                                <select value={form.vendor_id} onChange={e => updateField('vendor_id', e.target.value)} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" required>
                                    <option value="">Select Vendor</option>
                                    {vendors.map(v => <option key={v.id} value={v.id}>{v.name}</option>)}
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Currency *</label>
                                <select value={form.currency_id} onChange={e => updateField('currency_id', e.target.value)} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" required>
                                    <option value="">Select Currency</option>
                                    {currencies.map(c => <option key={c.id} value={c.id}>{c.code} - {c.name}</option>)}
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Quotation Date *</label>
                                <input type="date" value={form.quotation_date} onChange={e => updateField('quotation_date', e.target.value)} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" required />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Validity Date</label>
                                <input type="date" value={form.validity_date} onChange={e => updateField('validity_date', e.target.value)} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" />
                            </div>
                            <div className="md:col-span-2">
                                <label className="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                <input type="text" value={form.notes} onChange={e => updateField('notes', e.target.value)} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" placeholder="Optional notes..." />
                            </div>
                        </div>
                    </div>
                    <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <div className="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                            <h3 className="font-semibold text-gray-900">Quotation Lines</h3>
                            <Button type="button" onClick={addLine} className="flex items-center gap-2 text-sm bg-indigo-50 text-indigo-700 hover:bg-indigo-100"><Plus className="h-4 w-4" /> Add Line</Button>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead className="bg-gray-50"><tr>
                                    <th className="px-3 py-3 text-left font-medium text-gray-600 w-8">#</th>
                                    <th className="px-3 py-3 text-left font-medium text-gray-600">Product</th>
                                    <th className="px-3 py-3 text-right font-medium text-gray-600 w-24">Qty</th>
                                    <th className="px-3 py-3 text-right font-medium text-gray-600 w-36">Price</th>
                                    <th className="px-3 py-3 text-right font-medium text-gray-600 w-32">Subtotal</th>
                                    <th className="px-3 py-3 w-12"></th>
                                </tr></thead>
                                <tbody className="divide-y divide-gray-100">
                                    {form.lines.map((line, i) => (
                                        <tr key={i}>
                                            <td className="px-3 py-2 text-gray-500">{i + 1}</td>
                                            <td className="px-3 py-2">
                                                <select value={line.product_id} onChange={e => { updateLine(i, 'product_id', e.target.value); const p = products.find(x => x.id == e.target.value); if (p) { updateLine(i, 'description', p.name); updateLine(i, 'price', p.price || 0); } }} className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" required>
                                                    <option value="">Select</option>
                                                    {products.map(p => <option key={p.id} value={p.id}>{p.name}</option>)}
                                                </select>
                                            </td>
                                            <td className="px-3 py-2"><input type="number" value={line.quantity} onChange={e => updateLine(i, 'quantity', e.target.value)} className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm text-right" min="0.01" required /></td>
                                            <td className="px-3 py-2"><input type="number" value={line.price} onChange={e => updateLine(i, 'price', e.target.value)} className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm text-right" min="0" required /></td>
                                            <td className="px-3 py-2 text-right font-medium">{formatCurrency((Number(line.quantity) || 0) * (Number(line.price) || 0))}</td>
                                            <td className="px-3 py-2"><button type="button" onClick={() => removeLine(i)} className="p-1 rounded text-red-400 hover:text-red-600" disabled={form.lines.length <= 1}><Trash2 className="h-4 w-4" /></button></td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                        <div className="px-6 py-3 bg-gray-50 text-right text-sm">
                            <span className="text-gray-500">Total: </span>
                            <span className="font-bold text-indigo-600 text-lg">{formatCurrency(form.lines.reduce((s, l) => s + (Number(l.quantity) || 0) * (Number(l.price) || 0), 0))}</span>
                        </div>
                    </div>
                    <div className="flex justify-end gap-3">
                        <Button type="button" onClick={() => { setView(selectedId ? 'detail' : 'list'); setError(null); }} className="bg-gray-100 text-gray-700 hover:bg-gray-200">Cancel</Button>
                        <Button type="submit" disabled={saving}>{saving ? 'Saving...' : (isEdit ? 'Update' : 'Create')}</Button>
                    </div>
                </form>
            </div>
        );
    }

    if (view === 'detail' && detail) {
        const status = detail.status;
        const canEdit = status === 'draft';
        const canSend = status === 'draft';
        const canAccept = status === 'received';
        const canReject = status === 'received';
        return (
            <div className="space-y-6">
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div className="flex items-center gap-3">
                        <button onClick={() => { setView('list'); setDetail(null); }} className="p-2 rounded-lg hover:bg-gray-100"><ArrowLeft className="h-5 w-5" /></button>
                        <div><h1 className="text-2xl font-bold text-gray-900">{detail.quotation_number}</h1><StatusBadge status={detail.status} label={detail.status_label} /></div>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        {canEdit && <Button onClick={() => { initForm(detail.id); setView('edit'); }} className="flex items-center gap-2 bg-blue-50 text-blue-700 hover:bg-blue-100"><Edit2 className="h-4 w-4" /> Edit</Button>}
                        {canSend && <Button onClick={() => handleAction('send', detail.id)} className="flex items-center gap-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100"><Send className="h-4 w-4" /> Send</Button>}
                        {canAccept && <Button onClick={() => handleAction('accept', detail.id)} className="flex items-center gap-2 bg-green-50 text-green-700 hover:bg-green-100"><CheckCircle className="h-4 w-4" /> Accept</Button>}
                        {canReject && <Button onClick={() => handleAction('reject', detail.id)} className="flex items-center gap-2 bg-red-50 text-red-700 hover:bg-red-100"><XCircle className="h-4 w-4" /> Reject</Button>}
                        {canEdit && <Button onClick={() => handleDelete(detail.id)} className="flex items-center gap-2 bg-red-50 text-red-700 hover:bg-red-100"><Trash2 className="h-4 w-4" /> Delete</Button>}
                    </div>
                </div>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <InfoCard label="Vendor" value={detail.vendor?.name || '-'} />
                    <InfoCard label="Quotation Date" value={formatDate(detail.quotation_date)} />
                    <InfoCard label="Validity Date" value={formatDate(detail.validity_date)} />
                    <InfoCard label="Currency" value={detail.currency?.code || 'IDR'} />
                    <InfoCard label="Total" value={formatCurrency(detail.total_amount, detail.currency?.code)} />
                    <InfoCard label="Created By" value={detail.creator?.name || '-'} />
                </div>
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="px-6 py-4 border-b border-gray-200"><h3 className="font-semibold text-gray-900">Lines</h3></div>
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50"><tr>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">#</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Product</th>
                                <th className="px-4 py-3 text-right font-medium text-gray-600">Qty</th>
                                <th className="px-4 py-3 text-right font-medium text-gray-600">Price</th>
                                <th className="px-4 py-3 text-right font-medium text-gray-600">Total</th>
                            </tr></thead>
                            <tbody className="divide-y divide-gray-100">
                                {(detail.lines || []).map((l, i) => (
                                    <tr key={i} className="hover:bg-gray-50">
                                        <td className="px-4 py-3 text-gray-500">{l.line_number || i + 1}</td>
                                        <td className="px-4 py-3 font-medium">{l.product?.name || l.description}</td>
                                        <td className="px-4 py-3 text-right">{l.quantity} {l.uom?.symbol || ''}</td>
                                        <td className="px-4 py-3 text-right">{formatCurrency(l.price)}</td>
                                        <td className="px-4 py-3 text-right font-medium">{formatCurrency(l.subtotal || l.total)}</td>
                                    </tr>
                                ))}
                                {(detail.lines || []).length === 0 && <tr><td colSpan={5} className="px-4 py-8 text-center text-gray-400">No lines</td></tr>}
                            </tbody>
                        </table>
                    </div>
                </div>
                {detail.notes && <div className="bg-white rounded-xl border border-gray-200 p-6"><h3 className="font-semibold text-gray-900 mb-2">Notes</h3><p className="text-gray-600 text-sm">{detail.notes}</p></div>}
                {confirmAction && <Modal onClose={() => setConfirmAction(null)} title={`Confirm ${confirmAction.action}`}>
                    <p className="text-gray-600 mb-4">Are you sure you want to <strong>{confirmAction.action}</strong> this quotation?</p>
                    <div className="flex justify-end gap-3">
                        <Button onClick={() => setConfirmAction(null)} className="bg-gray-100 text-gray-700 hover:bg-gray-200">Cancel</Button>
                        <Button onClick={executeAction} disabled={saving}>{saving ? 'Processing...' : 'Confirm'}</Button>
                    </div>
                </Modal>}
            </div>
        );
    }

    // ─── LIST VIEW ─────────────────────────────────
    return (
        <div className="space-y-6">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div><h1 className="text-2xl font-bold text-gray-900">Supplier Quotations</h1><p className="text-sm text-gray-500">{total} quotations total</p></div>
                <Button onClick={() => { setSelectedId(null); setView('create'); }} className="flex items-center gap-2"><Plus className="h-4 w-4" /> New Quotation</Button>
            </div>
            <div className="flex flex-col sm:flex-row gap-3">
                <div className="relative flex-1">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <input type="text" value={search} onChange={e => handleSearchChange(e.target.value)} placeholder="Search quotations..." className="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" />
                </div>
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
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Date</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Vendor</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                                <th className="px-4 py-3 text-right font-medium text-gray-600">Total</th>
                                <th className="px-4 py-3 text-center font-medium text-gray-600">Actions</th>
                            </tr></thead>
                            <tbody className="divide-y divide-gray-100">
                                {quotations.map(q => (
                                    <tr key={q.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3"><button onClick={() => loadDetail(q.id)} className="font-medium text-indigo-600 hover:text-indigo-800">{q.quotation_number}</button></td>
                                        <td className="px-4 py-3 text-gray-600">{formatDate(q.quotation_date)}</td>
                                        <td className="px-4 py-3">{q.vendor?.name || '-'}</td>
                                        <td className="px-4 py-3"><StatusBadge status={q.status} label={q.status_label} /></td>
                                        <td className="px-4 py-3 text-right font-medium">{formatCurrency(q.total_amount, q.currency?.code)}</td>
                                        <td className="px-4 py-3"><div className="flex items-center justify-center gap-1">
                                            <button onClick={() => loadDetail(q.id)} className="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100"><Eye className="h-4 w-4" /></button>
                                            {q.status === 'draft' && <>
                                                <button onClick={() => { setSelectedId(q.id); initForm(q.id); setView('edit'); }} className="p-1.5 rounded-lg text-blue-500 hover:bg-blue-50"><Edit2 className="h-4 w-4" /></button>
                                                <button onClick={() => handleDelete(q.id)} className="p-1.5 rounded-lg text-red-500 hover:bg-red-50"><Trash2 className="h-4 w-4" /></button>
                                            </>}
                                        </div></td>
                                    </tr>
                                ))}
                                {quotations.length === 0 && <tr><td colSpan={6} className="px-4 py-12 text-center text-gray-400">No quotations found.</td></tr>}
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
