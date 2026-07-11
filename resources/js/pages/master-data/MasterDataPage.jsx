import { useState } from 'react';
import { Plus, Download, Upload } from 'lucide-react';
import { DataTable, Pagination, SearchFilter, Button, Modal, Alert, ConfirmDialog } from '../../components/ui';
import { useMasterData } from '../../hooks/useMasterData';
import { masterDataApi } from '../../api/endpoints';
import MasterDataForm from './MasterDataForm';

export default function MasterDataPage({ config }) {
    const {
        title, moduleName, icon: Icon, columns, filters, formFields,
        exportable = true,
    } = config;

    const {
        data, loading, saving, deleting, error, setError,
        currentPage, lastPage, total, perPage,
        search, filterValues, sortKey, sortDir,
        handlePageChange, handleSort, handleSearchChange, handleFilterChange,
        store, update, destroy, toggleStatus,
    } = useMasterData(moduleName);

    const [showForm, setShowForm] = useState(false);
    const [editItem, setEditItem] = useState(null);
    const [deleteItem, setDeleteItem] = useState(null);
    const [formErrors, setFormErrors] = useState({});
    const [exporting, setExporting] = useState(false);

    const handleExport = async () => {
        setExporting(true);
        try {
            const res = await masterDataApi.export(moduleName, {
                search: search || undefined,
                ...Object.fromEntries(
                    Object.entries(filterValues).filter(([, v]) => v !== '' && v != null)
                ),
            });
            const url = URL.createObjectURL(new Blob([res.data], { type: 'text/csv' }));
            const a = document.createElement('a');
            a.href = url;
            a.download = `${moduleName}_${new Date().toISOString().slice(0, 10)}.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        } catch (err) {
            console.error('Failed to export:', err);
            alert('Failed to export data');
        } finally {
            setExporting(false);
        }
    };

    const handleCreate = () => {
        setEditItem(null);
        setFormErrors({});
        setShowForm(true);
    };

    const handleEdit = (row) => {
        setEditItem(row);
        setFormErrors({});
        setShowForm(true);
    };

    const handleSubmit = async (formData) => {
        try {
            setFormErrors({});
            if (editItem) {
                await update(editItem.id, formData);
            } else {
                await store(formData);
            }
            setShowForm(false);
            setEditItem(null);
        } catch (err) {
            if (err.response?.status === 422) {
                setFormErrors(err.response.data.errors || {});
            }
        }
    };

    const handleDelete = async () => {
        if (!deleteItem) return;
        try {
            await destroy(deleteItem.id);
            setDeleteItem(null);
        } catch {
            // error is set in hook
        }
    };

    // Add actions column
    const tableColumns = [
        ...columns,
        {
            key: 'is_active',
            label: 'Status',
            type: 'status',
            sortable: true,
            width: '100px',
            render: (row) => (
                <button
                    onClick={() => toggleStatus(row.id)}
                    className="cursor-pointer"
                >
                    <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${
                        row.is_active
                            ? 'bg-green-100 text-green-700 hover:bg-green-200'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                    } transition-colors`}>
                        {row.is_active ? 'Active' : 'Inactive'}
                    </span>
                </button>
            ),
        },
        {
            key: 'actions',
            label: 'Actions',
            width: '120px',
            render: (row) => (
                <div className="flex items-center gap-1">
                    <Button size="sm" variant="ghost" onClick={() => handleEdit(row)}>
                        Edit
                    </Button>
                    <Button size="sm" variant="ghost" className="text-red-600 hover:text-red-700" onClick={() => setDeleteItem(row)}>
                        Delete
                    </Button>
                </div>
            ),
        },
    ];

    return (
        <div className="space-y-6">
            {/* Page Header */}
            <div className="flex items-center justify-between">
                <div className="flex items-center gap-3">
                    {Icon && (
                        <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100">
                            <Icon className="h-5 w-5 text-indigo-600" />
                        </div>
                    )}
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">{title}</h1>
                        <p className="text-sm text-gray-500">{total} records</p>
                    </div>
                </div>
                <div className="flex items-center gap-2">
                    {exportable && (
                        <Button variant="secondary" size="sm" onClick={handleExport} disabled={exporting}>
                            <Download className="h-4 w-4 mr-1" />
                            {exporting ? 'Exporting...' : 'Export'}
                        </Button>
                    )}
                    <Button onClick={handleCreate}>
                        <Plus className="h-4 w-4 mr-1" />
                        Add {title}
                    </Button>
                </div>
            </div>

            {/* Error Alert */}
            {error && (
                <Alert type="error" onClose={() => setError(null)}>
                    {error}
                </Alert>
            )}

            {/* Search & Filters */}
            <SearchFilter
                search={search}
                onSearchChange={handleSearchChange}
                searchPlaceholder={`Search ${title.toLowerCase()}...`}
                filters={filters || []}
                filterValues={filterValues}
                onFilterChange={handleFilterChange}
            />

            {/* Table */}
            <div className="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <DataTable
                    columns={tableColumns}
                    data={data}
                    loading={loading}
                    emptyMessage={`No ${title.toLowerCase()} found`}
                    onSort={handleSort}
                    sortKey={sortKey}
                    sortDir={sortDir}
                />
                <Pagination
                    currentPage={currentPage}
                    lastPage={lastPage}
                    total={total}
                    perPage={perPage}
                    onPageChange={handlePageChange}
                />
            </div>

            {/* Form Modal */}
            <Modal
                open={showForm}
                onClose={() => { setShowForm(false); setEditItem(null); }}
                title={editItem ? `Edit ${title}` : `Add ${title}`}
                size="lg"
            >
                <MasterDataForm
                    fields={formFields}
                    initialData={editItem}
                    errors={formErrors}
                    loading={saving}
                    onSubmit={handleSubmit}
                    onCancel={() => { setShowForm(false); setEditItem(null); }}
                />
            </Modal>

            {/* Delete Confirmation */}
            <ConfirmDialog
                open={!!deleteItem}
                onClose={() => setDeleteItem(null)}
                onConfirm={handleDelete}
                message={`Are you sure you want to delete "${deleteItem?.name}"? This action cannot be undone.`}
                loading={deleting}
            />
        </div>
    );
}
