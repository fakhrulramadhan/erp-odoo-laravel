import { useState, useEffect } from 'react';
import {
    Plus, Search, Eye, Edit2, Trash2, ArrowLeft,
    Package, ArrowRightLeft, TrendingDown, BarChart3
} from 'lucide-react';
import { assetsApi, assetCategoriesApi, masterDataApi } from '../../api/endpoints';
import { useCrudApi } from '../../hooks/useCrudApi';
import { StatusBadge, formatCurrency, formatDate } from '../../components/ui/StatusBadge';
import { Button } from '../../components/ui/Button';
import { Modal } from '../../components/ui/Modal';
import { Spinner } from '../../components/ui/Loader';

const ASSET_STATUS_OPTIONS = [
    { value: '', label: 'All Status' },
    { value: 'draft', label: 'Draft' },
    { value: 'active', label: 'Active' },
    { value: 'in_maintenance', label: 'In Maintenance' },
    { value: 'transferred', label: 'Transferred' },
    { value: 'disposed', label: 'Disposed' },
    { value: 'retired', label: 'Retired' },
];

export default function AssetsPage() {
    const {
        data: assets, loading, saving, error, setError,
        currentPage, lastPage, total,
        search, filterValues,
        fetchData, handlePageChange, handleSearchChange, handleFilterChange,
        store, update, destroy, performAction,
    } = useCrudApi(assetsApi);

    const [view, setView] = useState('list');
    const [selectedId, setSelectedId] = useState(null);
    const [detail, setDetail] = useState(null);
    const [confirmAction, setConfirmAction] = useState(null);
    const [categories, setCategories] = useState([]);
    const [branches, setBranches] = useState([]);

    useEffect(() => {
        Promise.all([
            assetCategoriesApi.list({ per_page: 999 }),
            masterDataApi.list('branches', { per_page: 999 }),
        ]).then(([c, b]) => {
            const ex = (res) => { const d = res.data?.data; return Array.isArray(d) ? d : (d?.data || []); };
            setCategories(ex(c));
            setBranches(ex(b));
        }).catch(() => {});
    }, []);

    const loadDetail = async (id) => {
        try { const res = await assetsApi.show(id); setDetail(res.data.data); setSelectedId(id); setView('detail'); } catch {}
    };

    const handleAction = (action, id) => setConfirmAction({ action, id });
    const executeAction = async () => {
        if (!confirmAction) return;
        const { action, id } = confirmAction;
        const map = { activate: assetsApi.activate, depreciate: assetsApi.depreciate };
        const result = await performAction(map[action], id);
        if (result && view === 'detail' && id === selectedId) loadDetail(id);
        setConfirmAction(null);
    };

    const handleDelete = async (id) => {
        if (window.confirm('Delete this asset?')) { const ok = await destroy(id); if (ok && view === 'detail') setView('list'); }
    };

    const statusFilter = filterValues.status || '';

    if (view === 'create' || view === 'edit') {
        return <AssetForm isEdit={view === 'edit'} itemId={selectedId} categories={categories} branches={branches}
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
                            <h1 className="text-2xl font-bold text-gray-900">{detail.name}</h1>
                            <p className="text-sm text-gray-500">{detail.asset_number}</p>
                            <StatusBadge status={detail.status} label={detail.status_label} />
                        </div>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        {s === 'draft' && <Button onClick={() => { setSelectedId(detail.id); setView('edit'); }} className="flex items-center gap-2 bg-blue-50 text-blue-700 hover:bg-blue-100"><Edit2 className="h-4 w-4" /> Edit</Button>}
                        {s === 'draft' && <Button onClick={() => handleAction('activate', detail.id)} className="flex items-center gap-2 bg-green-50 text-green-700 hover:bg-green-100"><Package className="h-4 w-4" /> Activate</Button>}
                        {s === 'active' && <Button onClick={() => handleAction('depreciate', detail.id)} className="flex items-center gap-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100"><BarChart3 className="h-4 w-4" /> Depreciate</Button>}
                        {s === 'draft' && <Button onClick={() => handleDelete(detail.id)} className="flex items-center gap-2 bg-red-50 text-red-700 hover:bg-red-100"><Trash2 className="h-4 w-4" /> Delete</Button>}
                    </div>
                </div>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <InfoCard label="Asset Number" value={detail.asset_number} />
                    <InfoCard label="Category" value={detail.category?.name || '-'} />
                    <InfoCard label="Acquisition Date" value={formatDate(detail.acquisition_date)} />
                    <InfoCard label="Acquisition Value" value={formatCurrency(detail.acquisition_value)} />
                    <InfoCard label="Salvage Value" value={formatCurrency(detail.salvage_value)} />
                    <InfoCard label="Depreciation Method" value={detail.depreciation_method_label || detail.depreciation_method || '-'} />
                    <InfoCard label="Useful Life (months)" value={detail.useful_life_months || '-'} />
                    <InfoCard label="Monthly Depreciation" value={formatCurrency(detail.monthly_depreciation)} />
                    <InfoCard label="Book Value" value={formatCurrency(detail.book_value)} />
                </div>
                {/* Depreciation History */}
                {detail.depreciations && detail.depreciations.length > 0 && (
                    <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <div className="px-6 py-4 border-b border-gray-200"><h3 className="font-semibold text-gray-900">Depreciation History</h3></div>
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead className="bg-gray-50"><tr>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Period</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">Amount</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">Accumulated</th>
                                    <th className="px-4 py-3 text-right font-medium text-gray-600">Book Value</th>
                                </tr></thead>
                                <tbody className="divide-y divide-gray-100">
                                    {detail.depreciations.map((d) => (
                                        <tr key={d.id} className="hover:bg-gray-50">
                                            <td className="px-4 py-3">{d.depreciation_date}</td>
                                            <td className="px-4 py-3 text-right">{formatCurrency(d.amount)}</td>
                                            <td className="px-4 py-3 text-right">{formatCurrency(d.accumulated_depreciation)}</td>
                                            <td className="px-4 py-3 text-right font-medium">{formatCurrency(d.book_value)}</td>
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
                <div><h1 className="text-2xl font-bold text-gray-900">Fixed Assets</h1><p className="text-sm text-gray-500">{total} assets total</p></div>
                <Button onClick={() => { setSelectedId(null); setView('create'); }} className="flex items-center gap-2"><Plus className="h-4 w-4" /> New Asset</Button>
            </div>
            <div className="flex flex-col sm:flex-row gap-3">
                <div className="relative flex-1"><Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" /><input type="text" value={search} onChange={(e) => handleSearchChange(e.target.value)} placeholder="Search..." className="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" /></div>
                <select value={statusFilter} onChange={(e) => handleFilterChange({ ...filterValues, status: e.target.value })} className="rounded-lg border border-gray-300 px-3 py-2 text-sm">{ASSET_STATUS_OPTIONS.map(o => <option key={o.value} value={o.value}>{o.label}</option>)}</select>
            </div>
            {loading ? <div className="flex justify-center py-20"><Spinner /></div> : (
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 border-b border-gray-200"><tr>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Asset Number</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Name</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Category</th>
                                <th className="px-4 py-3 text-right font-medium text-gray-600">Value</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                                <th className="px-4 py-3 text-center font-medium text-gray-600">Actions</th>
                            </tr></thead>
                            <tbody className="divide-y divide-gray-100">
                                {assets.map((a) => (
                                    <tr key={a.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3"><button onClick={() => loadDetail(a.id)} className="font-medium text-indigo-600 hover:text-indigo-800">{a.asset_number}</button></td>
                                        <td className="px-4 py-3 text-gray-800">{a.name}</td>
                                        <td className="px-4 py-3 text-gray-600">{a.category?.name || '-'}</td>
                                        <td className="px-4 py-3 text-right font-medium">{formatCurrency(a.acquisition_value)}</td>
                                        <td className="px-4 py-3"><StatusBadge status={a.status} label={a.status_label} /></td>
                                        <td className="px-4 py-3"><div className="flex items-center justify-center gap-1">
                                            <button onClick={() => loadDetail(a.id)} className="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100"><Eye className="h-4 w-4" /></button>
                                            {a.status === 'draft' && <button onClick={() => { setSelectedId(a.id); setView('edit'); }} className="p-1.5 rounded-lg text-blue-500 hover:bg-blue-50"><Edit2 className="h-4 w-4" /></button>}
                                        </div></td>
                                    </tr>
                                ))}
                                {assets.length === 0 && <tr><td colSpan={6} className="px-4 py-12 text-center text-gray-400">No assets found.</td></tr>}
                            </tbody>
                        </table>
                    </div>
                </div>
            )}
            {confirmAction && (
                <Modal onClose={() => setConfirmAction(null)} title={`Confirm ${confirmAction.action}`}>
                    <p className="text-gray-600 mb-4">Are you sure you want to <strong>{confirmAction.action}</strong> this asset?</p>
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

function AssetForm({ isEdit, itemId, categories, branches, onBack, onSave, saving, error }) {
    const [form, setForm] = useState({ name: '', asset_category_id: '', acquisition_date: '', acquisition_value: 0, salvage_value: 0, depreciation_method: 'straight_line', useful_life_months: 60, location: '', notes: '' });
    const [loadingForm, setLoadingForm] = useState(isEdit);

    useState(() => {
        if (isEdit && itemId) {
            assetsApi.show(itemId).then(res => {
                const d = res.data.data;
                setForm({ name: d.name || '', asset_category_id: d.asset_category_id || '', acquisition_date: d.acquisition_date || '', acquisition_value: d.acquisition_value || 0, salvage_value: d.salvage_value || 0, depreciation_method: d.depreciation_method || 'straight_line', useful_life_months: d.useful_life_months || 60, location: d.location || '', notes: d.notes || '' });
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
                <h1 className="text-2xl font-bold text-gray-900">{isEdit ? 'Edit Asset' : 'New Asset'}</h1>
            </div>
            {error && <div className="rounded-lg bg-red-50 p-4 text-red-700 text-sm"><p className="font-medium">{error.message}</p>{errorMessages.length > 0 && <ul className="mt-1 list-disc list-inside">{errorMessages.map((m, i) => <li key={i}>{m}</li>)}</ul>}</div>}
            <form onSubmit={async (e) => { e.preventDefault(); await onSave(form); }} className="space-y-6">
                <div className="bg-white rounded-xl border border-gray-200 p-6">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Name *</label><input type="text" value={form.name} onChange={e => setForm(p => ({ ...p, name: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required /></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Category *</label><select value={form.asset_category_id} onChange={e => setForm(p => ({ ...p, asset_category_id: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required><option value="">Select Category</option>{categories.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}</select></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Acquisition Date *</label><input type="date" value={form.acquisition_date} onChange={e => setForm(p => ({ ...p, acquisition_date: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required /></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Acquisition Value *</label><input type="number" min="0" step="0.01" value={form.acquisition_value} onChange={e => setForm(p => ({ ...p, acquisition_value: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required /></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Salvage Value</label><input type="number" min="0" step="0.01" value={form.salvage_value} onChange={e => setForm(p => ({ ...p, salvage_value: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Depreciation Method</label><select value={form.depreciation_method} onChange={e => setForm(p => ({ ...p, depreciation_method: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><option value="straight_line">Straight Line</option><option value="declining_balance">Declining Balance</option><option value="double_declining_balance">Double Declining</option><option value="units_of_production">Units of Production</option></select></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Useful Life (months)</label><input type="number" min="1" value={form.useful_life_months} onChange={e => setForm(p => ({ ...p, useful_life_months: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Location</label><input type="text" value={form.location} onChange={e => setForm(p => ({ ...p, location: e.target.value }))} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
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
