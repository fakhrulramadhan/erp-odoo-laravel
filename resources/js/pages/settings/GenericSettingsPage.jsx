import { useState } from 'react';
import { Plus } from 'lucide-react';
import { DataTable, Pagination, SearchFilter, Button, Modal, Alert, ConfirmDialog } from '../../components/ui';
import { useCrudApi } from '../../hooks/useCrudApi';
import MasterDataForm from '../master-data/MasterDataForm';

export default function GenericSettingsPage({ config, apiModule }) {
    const {
        title, icon: Icon, columns, filters, formFields,
        canCreate = true, canEdit = true, canDelete = true,
    } = config;

    const {
        data, loading, saving, error, setError,
        currentPage, lastPage, total, perPage,
        search, filterValues,
        handlePageChange, handleSearchChange, handleFilterChange,
        store, update, destroy,
    } = useCrudApi(apiModule);

    const [showForm, setShowForm] = useState(false);
    const [editItem, setEditItem] = useState(null);
    const [deleteItem, setDeleteItem] = useState(null);
    const [formErrors, setFormErrors] = useState({});

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

    // Build table columns with actions
    const tableColumns = [
        ...columns,
        ...((canEdit || canDelete) ? [{
            key: 'actions',
            label: 'Actions',
            width: '150px',
            render: (row) => (
                <div className="flex items-center gap-1">
                    {canEdit && (
                        <Button size="sm" variant="ghost" onClick={() => handleEdit(row)}>
                            Edit
                        </Button>
                    )}
                    {canDelete && (
                        <Button size="sm" variant="ghost" className="text-red-600 hover:text-red-700" onClick={() => setDeleteItem(row)}>
                            Delete
                        </Button>
                    )}
                </div>
            ),
        }] : []),
    ];

    const errorMessage = error
        ? (typeof error === 'string' ? error : error.message || 'An error occurred')
        : null;

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
                        <p className="text-sm text-gray-500">{total || data.length} records</p>
                    </div>
                </div>
                <div className="flex items-center gap-2">
                    {canCreate && formFields && (
                        <Button onClick={handleCreate}>
                            <Plus className="h-4 w-4 mr-1" />
                            Add {title}
                        </Button>
                    )}
                </div>
            </div>

            {/* Error Alert */}
            {errorMessage && (
                <Alert type="error" onClose={() => setError(null)}>
                    {errorMessage}
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
                />
                {lastPage > 1 && (
                    <Pagination
                        currentPage={currentPage}
                        lastPage={lastPage}
                        total={total}
                        perPage={perPage}
                        onPageChange={handlePageChange}
                    />
                )}
            </div>

            {/* Form Modal */}
            {formFields && (
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
            )}

            {/* Delete Confirmation */}
            <ConfirmDialog
                open={!!deleteItem}
                onClose={() => setDeleteItem(null)}
                onConfirm={handleDelete}
                message={`Are you sure you want to delete "${deleteItem?.name || deleteItem?.email || 'this item'}"? This action cannot be undone.`}
            />
        </div>
    );
}
