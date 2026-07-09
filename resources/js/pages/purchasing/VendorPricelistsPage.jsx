import { useState, useEffect } from 'react';
import { Plus, Search, Eye, Edit2, Trash2, ArrowLeft, DollarSign } from 'lucide-react';
import { vendorPricelistsApi, masterDataApi } from '../../api/endpoints';
import { useCrudApi } from '../../hooks/useCrudApi';
import { formatDate } from '../../components/ui/StatusBadge';
import { Button } from '../../components/ui/Button';
import { Spinner } from '../../components/ui/Loader';

export default function VendorPricelistsPage() {
    const {
        data: pricelists, loading, saving, error, setError,
        currentPage, lastPage, total,
        search, filterValues,
        fetchData, handlePageChange, handleSearchChange, handleFilterChange,
        store, update, destroy,
    } = useCrudApi(vendorPricelistsApi);

    const [view, setView] = useState('list');
    const [selectedId, setSelectedId] = useState(null);
    const [vendors, setVendors] = useState([]);
    const [products, setProducts] = useState([]);

    useEffect(() => {
        async function loadLookups() {
            try {
                const [v, p] = await Promise.all([
                    masterDataApi.list('vendors', { per_page: 999 }),
                    masterDataApi.list('products', { per_page: 999 }),
                ]);
                const extract = (res) => { const d = res.data?.data; return Array.isArray(d) ? d : (d?.data || []); };
                setVendors(extract(v));
                setProducts(extract(p));
            } catch {}
        }
        loadLookups();
    }, []);

    const [form, setForm] = useState({
        vendor_id: '', product_id: '', price: '', min_quantity: 1,
        valid_from: '', valid_to: '', currency_code: 'IDR', is_active: true,
    });
    const [loadingForm, setLoadingForm] = useState(false);

    const initForm = async (id) => {
        setLoadingForm(true);
        try {
            const res = await vendorPricelistsApi.show(id);
            const p = res.data.data;
            setForm({
                vendor_id: p.vendor_id || '', product_id: p.product_id || '',
                price: p.price || '', min_quantity: p.min_quantity || 1,
                valid_from: p.valid_from || '', valid_to: p.valid_to || '',
                currency_code: p.currency_code || 'IDR', is_active: p.is_active ?? true,
            });
            setSelectedId(id);
            setView('form');
        } catch {} finally { setLoadingForm(false); }
    };

    const resetForm = () => {
        setForm({ vendor_id: '', product_id: '', price: '', min_quantity: 1, valid_from: '', valid_to: '', currency_code: 'IDR', is_active: true });
        setSelectedId(null);
    };

    const updateField = (f, v) => setForm(p => ({ ...p, [f]: v }));

    if (view === 'form') {
        const isEdit = !!selectedId;
        const errorMessages = error?.errors ? Object.values(error.errors).flat() : [];
        return (
            <div className="space-y-6">
                <div className="flex items-center gap-3">
                    <button onClick={() => { setView('list'); resetForm(); setError(null); }} className="p-2 rounded-lg hover:bg-gray-100"><ArrowLeft className="h-5 w-5" /></button>
                    <h1 className="text-2xl font-bold text-gray-900">{isEdit ? 'Edit Pricelist' : 'New Pricelist'}</h1>
                </div>
                {error && <div className="rounded-lg bg-red-50 p-4 text-red-700 text-sm">
                    <p className="font-medium">{error.message || 'Error'}</p>
                    {errorMessages.length > 0 && <ul className="mt-1 list-disc list-inside">{errorMessages.map((m, i) => <li key={i}>{m}</li>)}</ul>}
                </div>}
                <form onSubmit={async (e) => {
                    e.preventDefault();
                    let ok;
                    if (isEdit) { ok = await update(selectedId, form); }
                    else { ok = await store(form); }
                    if (ok) { setView('list'); resetForm(); }
                }} className="space-y-6">
                    <div className="bg-white rounded-xl border border-gray-200 p-6">
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Vendor *</label>
                                <select value={form.vendor_id} onChange={e => updateField('vendor_id', e.target.value)} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                                    <option value="">Select Vendor</option>
                                    {vendors.map(v => <option key={v.id} value={v.id}>{v.name}</option>)}
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Product *</label>
                                <select value={form.product_id} onChange={e => updateField('product_id', e.target.value)} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                                    <option value="">Select Product</option>
                                    {products.map(p => <option key={p.id} value={p.id}>{p.name}</option>)}
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Price *</label>
                                <input type="number" value={form.price} onChange={e => updateField('price', e.target.value)} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" min="0" required />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Min Quantity</label>
                                <input type="number" value={form.min_quantity} onChange={e => updateField('min_quantity', e.target.value)} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" min="1" />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Valid From</label>
                                <input type="date" value={form.valid_from} onChange={e => updateField('valid_from', e.target.value)} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Valid To</label>
                                <input type="date" value={form.valid_to} onChange={e => updateField('valid_to', e.target.value)} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                            </div>
                            <div className="flex items-center gap-3 pt-6">
                                <input type="checkbox" checked={form.is_active} onChange={e => updateField('is_active', e.target.checked)} className="rounded border-gray-300 text-indigo-600" />
                                <label className="text-sm text-gray-700">Active</label>
                            </div>
                        </div>
                    </div>
                    <div className="flex justify-end gap-3">
                        <Button type="button" onClick={() => { setView('list'); resetForm(); setError(null); }} className="bg-gray-100 text-gray-700 hover:bg-gray-200">Cancel</Button>
                        <Button type="submit" disabled={saving}>{saving ? 'Saving...' : (isEdit ? 'Update' : 'Create')}</Button>
                    </div>
                </form>
            </div>
        );
    }

    // ─── LIST ──────────────────────────────────────
    return (
        <div className="space-y-6">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div><h1 className="text-2xl font-bold text-gray-900">Vendor Pricelists</h1><p className="text-sm text-gray-500">{total} entries</p></div>
                <Button onClick={() => { resetForm(); setView('form'); }} className="flex items-center gap-2"><Plus className="h-4 w-4" /> New Pricelist</Button>
            </div>
            <div className="relative max-w-md">
                <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                <input type="text" value={search} onChange={e => handleSearchChange(e.target.value)} placeholder="Search pricelists..." className="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm" />
            </div>
            {loading ? <div className="flex justify-center py-20"><Spinner /></div> : (
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 border-b border-gray-200"><tr>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Vendor</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Product</th>
                                <th className="px-4 py-3 text-right font-medium text-gray-600">Price</th>
                                <th className="px-4 py-3 text-right font-medium text-gray-600">Min Qty</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Valid From</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Valid To</th>
                                <th className="px-4 py-3 text-center font-medium text-gray-600">Status</th>
                                <th className="px-4 py-3 text-center font-medium text-gray-600">Actions</th>
                            </tr></thead>
                            <tbody className="divide-y divide-gray-100">
                                {pricelists.map(p => (
                                    <tr key={p.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3 font-medium">{p.vendor?.name || '-'}</td>
                                        <td className="px-4 py-3">{p.product?.name || '-'}</td>
                                        <td className="px-4 py-3 text-right font-medium">Rp {Number(p.price || 0).toLocaleString('id-ID')}</td>
                                        <td className="px-4 py-3 text-right">{p.min_quantity || 1}</td>
                                        <td className="px-4 py-3 text-gray-600">{formatDate(p.valid_from)}</td>
                                        <td className="px-4 py-3 text-gray-600">{formatDate(p.valid_to)}</td>
                                        <td className="px-4 py-3 text-center">
                                            <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${p.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'}`}>
                                                {p.is_active ? 'Active' : 'Inactive'}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3"><div className="flex items-center justify-center gap-1">
                                            <button onClick={() => initForm(p.id)} className="p-1.5 rounded-lg text-blue-500 hover:bg-blue-50"><Edit2 className="h-4 w-4" /></button>
                                            <button onClick={async () => { if (window.confirm('Delete?')) { await destroy(p.id); } }} className="p-1.5 rounded-lg text-red-500 hover:bg-red-50"><Trash2 className="h-4 w-4" /></button>
                                        </div></td>
                                    </tr>
                                ))}
                                {pricelists.length === 0 && <tr><td colSpan={8} className="px-4 py-12 text-center text-gray-400">No pricelists found.</td></tr>}
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
