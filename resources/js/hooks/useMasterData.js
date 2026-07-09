import { useState, useEffect, useCallback } from 'react';
import { masterDataApi } from '../api/endpoints';

export function useMasterData(moduleName) {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [deleting, setDeleting] = useState(false);
    const [error, setError] = useState(null);

    // Pagination
    const [currentPage, setCurrentPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [total, setTotal] = useState(0);
    const [perPage] = useState(15);

    // Filters & Sort
    const [search, setSearch] = useState('');
    const [filterValues, setFilterValues] = useState({});
    const [sortKey, setSortKey] = useState('created_at');
    const [sortDir, setSortDir] = useState('desc');

    const fetchData = useCallback(async (page = 1) => {
        setLoading(true);
        setError(null);
        try {
            const params = {
                page,
                per_page: perPage,
                search: search || undefined,
                sort_by: sortKey,
                sort_dir: sortDir,
                ...Object.fromEntries(
                    Object.entries(filterValues).filter(([, v]) => v !== '' && v != null)
                ),
            };
            const res = await masterDataApi.list(moduleName, params);
            const json = res.data;
            const payload = json.data || {};
            setData(Array.isArray(payload) ? payload : (payload.data || []));
            setCurrentPage(payload.meta?.current_page || payload.current_page || 1);
            setLastPage(payload.meta?.last_page || payload.last_page || 1);
            setTotal(payload.meta?.total || payload.total || 0);
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to fetch data');
            setData([]);
        } finally {
            setLoading(false);
        }
    }, [moduleName, search, filterValues, sortKey, sortDir, perPage]);

    useEffect(() => {
        fetchData(1);
    }, [fetchData]);

    const handlePageChange = (page) => {
        fetchData(page);
    };

    const handleSort = (key, dir) => {
        setSortKey(key);
        setSortDir(dir);
    };

    const handleSearchChange = (val) => {
        setSearch(val);
    };

    const handleFilterChange = (vals) => {
        setFilterValues(vals);
    };

    const store = async (formData) => {
        setSaving(true);
        setError(null);
        try {
            await masterDataApi.store(moduleName, formData);
            await fetchData(1);
            return true;
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to create');
            throw err;
        } finally {
            setSaving(false);
        }
    };

    const update = async (id, formData) => {
        setSaving(true);
        setError(null);
        try {
            await masterDataApi.update(moduleName, id, formData);
            await fetchData(currentPage);
            return true;
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to update');
            throw err;
        } finally {
            setSaving(false);
        }
    };

    const destroy = async (id) => {
        setDeleting(true);
        setError(null);
        try {
            await masterDataApi.destroy(moduleName, id);
            await fetchData(currentPage);
            return true;
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to delete');
            throw err;
        } finally {
            setDeleting(false);
        }
    };

    const toggleStatus = async (id) => {
        try {
            await masterDataApi.toggleStatus(moduleName, id);
            await fetchData(currentPage);
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to toggle status');
        }
    };

    return {
        data, loading, saving, deleting, error, setError,
        currentPage, lastPage, total, perPage,
        search, filterValues, sortKey, sortDir,
        handlePageChange, handleSort, handleSearchChange, handleFilterChange,
        store, update, destroy, toggleStatus, fetchData,
    };
}
