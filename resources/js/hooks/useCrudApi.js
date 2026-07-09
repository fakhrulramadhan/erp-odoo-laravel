import { useState, useEffect, useCallback } from 'react';

export function useCrudApi(apiModule) {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState(null);

    // Pagination
    const [currentPage, setCurrentPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [total, setTotal] = useState(0);
    const [perPage] = useState(15);

    // Filters
    const [search, setSearch] = useState('');
    const [filterValues, setFilterValues] = useState({});

    const fetchData = useCallback(async (page = 1) => {
        setLoading(true);
        setError(null);
        try {
            const params = {
                page,
                per_page: perPage,
                search: search || undefined,
                ...Object.fromEntries(
                    Object.entries(filterValues).filter(([, v]) => v !== '' && v != null)
                ),
            };
            const res = await apiModule.list(params);
            const json = res.data;
            const payload = json.data || {};
            setData(Array.isArray(payload) ? payload : (payload.data || []));
            setCurrentPage(payload.meta?.current_page || 1);
            setLastPage(payload.meta?.last_page || 1);
            setTotal(payload.meta?.total || 0);
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to fetch data');
            setData([]);
        } finally {
            setLoading(false);
        }
    }, [apiModule, search, filterValues, perPage]);

    useEffect(() => {
        fetchData(1);
    }, [fetchData]);

    const handlePageChange = (page) => fetchData(page);
    const handleSearchChange = (val) => setSearch(val);
    const handleFilterChange = (vals) => setFilterValues(vals);

    const store = async (formData) => {
        setSaving(true);
        setError(null);
        try {
            await apiModule.store(formData);
            await fetchData(1);
            return true;
        } catch (err) {
            const msg = err.response?.data?.message || 'Failed to save';
            const errors = err.response?.data?.errors || {};
            setError({ message: msg, errors });
            return false;
        } finally {
            setSaving(false);
        }
    };

    const update = async (id, formData) => {
        setSaving(true);
        setError(null);
        try {
            await apiModule.update(id, formData);
            await fetchData(currentPage);
            return true;
        } catch (err) {
            const msg = err.response?.data?.message || 'Failed to update';
            const errors = err.response?.data?.errors || {};
            setError({ message: msg, errors });
            return false;
        } finally {
            setSaving(false);
        }
    };

    const destroy = async (id) => {
        try {
            await apiModule.destroy(id);
            await fetchData(currentPage);
            return true;
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to delete');
            return false;
        }
    };

    const performAction = async (actionFn, id) => {
        setSaving(true);
        setError(null);
        try {
            const res = await actionFn(id);
            await fetchData(currentPage);
            return res;
        } catch (err) {
            const msg = err.response?.data?.message || 'Action failed';
            setError(msg);
            return false;
        } finally {
            setSaving(false);
        }
    };

    return {
        data, loading, saving, error, setError,
        currentPage, lastPage, total, perPage,
        search, filterValues,
        fetchData, handlePageChange, handleSearchChange, handleFilterChange,
        store, update, destroy, performAction,
    };
}
