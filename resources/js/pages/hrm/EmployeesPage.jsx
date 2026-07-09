import { useState } from 'react';
import { Plus, Search, Eye, Edit2, Trash2, UserX, Users } from 'lucide-react';
import { employeesApi, settingsApi } from '../../api/endpoints';
import { useCrudApi } from '../../hooks/useCrudApi';
import { StatusBadge, formatDate, formatCurrency } from '../../components/ui/StatusBadge';
import { Button } from '../../components/ui/Button';
import { Spinner } from '../../components/ui/Loader';

const STATUS_OPTIONS = [
    { value: '', label: 'All Status' },
    { value: 'active', label: 'Active' },
    { value: 'on_leave', label: 'On Leave' },
    { value: 'resigned', label: 'Resigned' },
    { value: 'terminated', label: 'Terminated' },
];

const EMPLOYMENT_TYPE_OPTIONS = [
    { value: '', label: 'All Types' },
    { value: 'full_time', label: 'Full Time' },
    { value: 'part_time', label: 'Part Time' },
    { value: 'contract', label: 'Contract' },
    { value: 'internship', label: 'Internship' },
    { value: 'freelance', label: 'Freelance' },
];

export default function EmployeesPage() {
    const {
        data: employees, loading, saving, error, setError,
        currentPage, lastPage, total,
        search, filterValues,
        fetchData, handlePageChange, handleSearchChange, handleFilterChange,
        store, update, destroy,
    } = useCrudApi(employeesApi);

    const [view, setView] = useState('list');
    const [selectedId, setSelectedId] = useState(null);
    const [detail, setDetail] = useState(null);
    const [detailLoading, setDetailLoading] = useState(false);
    const [form, setForm] = useState({});
    const [formErrors, setFormErrors] = useState({});

    // Lookups
    const [departments, setDepartments] = useState([]);
    const [positions, setPositions] = useState([]);
    const [branches, setBranches] = useState([]);

    const loadLookups = async () => {
        try {
            const [d, p, b] = await Promise.all([
                settingsApi.departments({ per_page: 999 }),
                settingsApi.positions({ per_page: 999 }),
                settingsApi.branches({ per_page: 999 }),
            ]);
            setDepartments(extractItems(d));
            setPositions(extractItems(p));
            setBranches(extractItems(b));
        } catch {}
    };

    const extractItems = (res) => {
        const p = res.data?.data;
        return Array.isArray(p) ? p : (p?.data || []);
    };

    const loadDetail = async (id) => {
        setDetailLoading(true);
        try {
            const res = await employeesApi.show(id);
            setDetail(res.data.data);
            setSelectedId(id);
            setView('detail');
        } catch {} finally {
            setDetailLoading(false);
        }
    };

    const openForm = async (emp = null) => {
        await loadLookups();
        if (emp) {
            setForm({
                first_name: emp.first_name || '', last_name: emp.last_name || '',
                email: emp.email || '', phone: emp.phone || '', gender: emp.gender || '',
                date_of_birth: emp.date_of_birth || '', address: emp.address || '',
                city: emp.city || '', national_id: emp.national_id || '',
                department_id: emp.department_id || '', position_id: emp.position_id || '',
                branch_id: emp.branch_id || '', employment_type: emp.employment_type || 'full_time',
                hire_date: emp.hire_date || '', basic_salary: emp.basic_salary || '',
                emergency_contact_name: emp.emergency_contact_name || '',
                emergency_contact_phone: emp.emergency_contact_phone || '',
                notes: emp.notes || '',
            });
            setSelectedId(emp.id);
            setView('edit');
        } else {
            setForm({
                first_name: '', last_name: '', email: '', phone: '', gender: '',
                date_of_birth: '', address: '', city: '', national_id: '',
                department_id: '', position_id: '', branch_id: '',
                employment_type: 'full_time', hire_date: '', basic_salary: '',
                emergency_contact_name: '', emergency_contact_phone: '', notes: '',
            });
            setSelectedId(null);
            setView('create');
        }
        setFormErrors({});
        setError(null);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setFormErrors({});
        let ok;
        if (view === 'edit') {
            ok = await update(selectedId, form);
            if (ok) loadDetail(selectedId);
        } else {
            ok = await store(form);
            if (ok) setView('list');
        }
        if (!ok && error?.errors) setFormErrors(error.errors);
    };

    const handleTerminate = async (id) => {
        const reason = window.prompt('Termination reason:');
        if (reason === null) return;
        try {
            await employeesApi.terminate(id, { resignation_reason: reason, resignation_date: new Date().toISOString().slice(0, 10) });
            loadDetail(id);
        } catch {}
    };

    const handleDelete = async (id) => {
        if (window.confirm('Delete this employee?')) {
            const ok = await destroy(id);
            if (ok && view === 'detail') setView('list');
        }
    };

    const statusFilter = filterValues.status || '';
    const typeFilter = filterValues.employment_type || '';

    // ─── FORM VIEW ──────────────────────────────
    if (view === 'create' || view === 'edit') {
        const f = form;
        const set = (k, v) => setForm({ ...form, [k]: v });
        return (
            <div className="mx-auto max-w-4xl space-y-6">
                <div className="flex items-center gap-3">
                    <button onClick={() => { setView(selectedId ? 'detail' : 'list'); setError(null); }}
                        className="text-gray-500 hover:text-gray-700">← Back</button>
                    <h1 className="text-2xl font-bold text-gray-900">{view === 'edit' ? 'Edit' : 'New'} Employee</h1>
                </div>
                {error && typeof error === 'string' && <div className="rounded-lg bg-red-50 p-4 text-red-700">{error}</div>}
                <form onSubmit={handleSubmit} className="space-y-6 rounded-xl border border-gray-200 bg-white p-6">
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <Field label="First Name *" error={formErrors.first_name}>
                            <input value={f.first_name} onChange={e => set('first_name', e.target.value)} className="input-field" required />
                        </Field>
                        <Field label="Last Name *" error={formErrors.last_name}>
                            <input value={f.last_name} onChange={e => set('last_name', e.target.value)} className="input-field" required />
                        </Field>
                        <Field label="Email *" error={formErrors.email}>
                            <input type="email" value={f.email} onChange={e => set('email', e.target.value)} className="input-field" required />
                        </Field>
                        <Field label="Phone" error={formErrors.phone}>
                            <input value={f.phone} onChange={e => set('phone', e.target.value)} className="input-field" />
                        </Field>
                        <Field label="Gender" error={formErrors.gender}>
                            <select value={f.gender} onChange={e => set('gender', e.target.value)} className="input-field">
                                <option value="">Select</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </Field>
                        <Field label="Date of Birth" error={formErrors.date_of_birth}>
                            <input type="date" value={f.date_of_birth} onChange={e => set('date_of_birth', e.target.value)} className="input-field" />
                        </Field>
                        <Field label="National ID" error={formErrors.national_id}>
                            <input value={f.national_id} onChange={e => set('national_id', e.target.value)} className="input-field" />
                        </Field>
                        <Field label="Employment Type *" error={formErrors.employment_type}>
                            <select value={f.employment_type} onChange={e => set('employment_type', e.target.value)} className="input-field" required>
                                <option value="full_time">Full Time</option>
                                <option value="part_time">Part Time</option>
                                <option value="contract">Contract</option>
                                <option value="internship">Internship</option>
                                <option value="freelance">Freelance</option>
                                <option value="outsourcing">Outsourcing</option>
                            </select>
                        </Field>
                        <Field label="Department" error={formErrors.department_id}>
                            <select value={f.department_id} onChange={e => set('department_id', e.target.value)} className="input-field">
                                <option value="">Select Department</option>
                                {departments.map(d => <option key={d.id} value={d.id}>{d.name}</option>)}
                            </select>
                        </Field>
                        <Field label="Position" error={formErrors.position_id}>
                            <select value={f.position_id} onChange={e => set('position_id', e.target.value)} className="input-field">
                                <option value="">Select Position</option>
                                {positions.map(p => <option key={p.id} value={p.id}>{p.name}</option>)}
                            </select>
                        </Field>
                        <Field label="Branch" error={formErrors.branch_id}>
                            <select value={f.branch_id} onChange={e => set('branch_id', e.target.value)} className="input-field">
                                <option value="">Select Branch</option>
                                {branches.map(b => <option key={b.id} value={b.id}>{b.name}</option>)}
                            </select>
                        </Field>
                        <Field label="Hire Date *" error={formErrors.hire_date}>
                            <input type="date" value={f.hire_date} onChange={e => set('hire_date', e.target.value)} className="input-field" required />
                        </Field>
                        <Field label="Basic Salary" error={formErrors.basic_salary}>
                            <input type="number" step="0.01" value={f.basic_salary} onChange={e => set('basic_salary', e.target.value)} className="input-field" />
                        </Field>
                        <Field label="City" error={formErrors.city}>
                            <input value={f.city} onChange={e => set('city', e.target.value)} className="input-field" />
                        </Field>
                    </div>
                    <Field label="Address" error={formErrors.address}>
                        <textarea value={f.address} onChange={e => set('address', e.target.value)} className="input-field" rows={2} />
                    </Field>
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <Field label="Emergency Contact Name" error={formErrors.emergency_contact_name}>
                            <input value={f.emergency_contact_name} onChange={e => set('emergency_contact_name', e.target.value)} className="input-field" />
                        </Field>
                        <Field label="Emergency Contact Phone" error={formErrors.emergency_contact_phone}>
                            <input value={f.emergency_contact_phone} onChange={e => set('emergency_contact_phone', e.target.value)} className="input-field" />
                        </Field>
                    </div>
                    <Field label="Notes" error={formErrors.notes}>
                        <textarea value={f.notes} onChange={e => set('notes', e.target.value)} className="input-field" rows={2} />
                    </Field>
                    <div className="flex justify-end gap-3">
                        <Button type="button" variant="secondary" onClick={() => { setView(selectedId ? 'detail' : 'list'); setError(null); }}>Cancel</Button>
                        <Button type="submit" disabled={saving}>{saving ? 'Saving...' : 'Save'}</Button>
                    </div>
                </form>
            </div>
        );
    }

    // ─── DETAIL VIEW ────────────────────────────
    if (view === 'detail' && detail) {
        const emp = detail;
        return (
            <div className="mx-auto max-w-4xl space-y-6">
                <div className="flex items-center gap-3">
                    <button onClick={() => { setView('list'); setDetail(null); }} className="text-gray-500 hover:text-gray-700">← Back</button>
                    <h1 className="text-2xl font-bold text-gray-900">{emp.full_name || `${emp.first_name} ${emp.last_name}`}</h1>
                    <StatusBadge status={emp.status} />
                </div>
                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div className="lg:col-span-2 space-y-4">
                        <div className="rounded-xl border border-gray-200 bg-white p-5">
                            <h3 className="mb-3 font-semibold text-gray-900">Personal Information</h3>
                            <dl className="grid grid-cols-2 gap-3 text-sm">
                                <dt className="text-gray-500">Employee Number</dt><dd className="text-gray-900">{emp.employee_number}</dd>
                                <dt className="text-gray-500">Email</dt><dd className="text-gray-900">{emp.email}</dd>
                                <dt className="text-gray-500">Phone</dt><dd className="text-gray-900">{emp.phone || '-'}</dd>
                                <dt className="text-gray-500">Gender</dt><dd className="text-gray-900 capitalize">{emp.gender || '-'}</dd>
                                <dt className="text-gray-500">Date of Birth</dt><dd className="text-gray-900">{formatDate(emp.date_of_birth)}</dd>
                                <dt className="text-gray-500">National ID</dt><dd className="text-gray-900">{emp.national_id || '-'}</dd>
                            </dl>
                        </div>
                        <div className="rounded-xl border border-gray-200 bg-white p-5">
                            <h3 className="mb-3 font-semibold text-gray-900">Employment Details</h3>
                            <dl className="grid grid-cols-2 gap-3 text-sm">
                                <dt className="text-gray-500">Department</dt><dd className="text-gray-900">{emp.department?.name || '-'}</dd>
                                <dt className="text-gray-500">Position</dt><dd className="text-gray-900">{emp.position?.name || '-'}</dd>
                                <dt className="text-gray-500">Branch</dt><dd className="text-gray-900">{emp.branch?.name || '-'}</dd>
                                <dt className="text-gray-500">Employment Type</dt><dd className="text-gray-900 capitalize">{emp.employment_type?.replace(/_/g, ' ')}</dd>
                                <dt className="text-gray-500">Hire Date</dt><dd className="text-gray-900">{formatDate(emp.hire_date)}</dd>
                                <dt className="text-gray-500">Basic Salary</dt><dd className="text-gray-900">{formatCurrency(emp.basic_salary)}</dd>
                                <dt className="text-gray-500">Manager</dt><dd className="text-gray-900">{emp.manager?.full_name || '-'}</dd>
                            </dl>
                        </div>
                    </div>
                    <div className="space-y-4">
                        <div className="rounded-xl border border-gray-200 bg-white p-5">
                            <h3 className="mb-3 font-semibold text-gray-900">Actions</h3>
                            <div className="space-y-2">
                                <Button onClick={() => openForm(emp)} className="w-full flex items-center justify-center gap-2">
                                    <Edit2 className="h-4 w-4" /> Edit
                                </Button>
                                {emp.status === 'active' && (
                                    <Button variant="danger" onClick={() => handleTerminate(emp.id)} className="w-full flex items-center justify-center gap-2">
                                        <UserX className="h-4 w-4" /> Terminate
                                    </Button>
                                )}
                                <Button variant="secondary" onClick={() => handleDelete(emp.id)} className="w-full flex items-center justify-center gap-2">
                                    <Trash2 className="h-4 w-4" /> Delete
                                </Button>
                            </div>
                        </div>
                        {emp.address && (
                            <div className="rounded-xl border border-gray-200 bg-white p-5">
                                <h3 className="mb-2 font-semibold text-gray-900">Address</h3>
                                <p className="text-sm text-gray-600">{emp.address}{emp.city ? `, ${emp.city}` : ''}</p>
                            </div>
                        )}
                        {emp.emergency_contact_name && (
                            <div className="rounded-xl border border-gray-200 bg-white p-5">
                                <h3 className="mb-2 font-semibold text-gray-900">Emergency Contact</h3>
                                <p className="text-sm text-gray-600">{emp.emergency_contact_name}</p>
                                <p className="text-sm text-gray-500">{emp.emergency_contact_phone}</p>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        );
    }

    // ─── LIST VIEW ──────────────────────────────
    return (
        <div className="space-y-6">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Employees</h1>
                    <p className="text-sm text-gray-500">{total} employees total</p>
                </div>
                <Button onClick={() => openForm()} className="flex items-center gap-2">
                    <Plus className="h-4 w-4" /> New Employee
                </Button>
            </div>

            <div className="flex flex-col sm:flex-row gap-3">
                <div className="relative flex-1">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <input type="text" value={search} onChange={e => handleSearchChange(e.target.value)}
                        placeholder="Search employees..." className="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" />
                </div>
                <select value={statusFilter} onChange={e => handleFilterChange({ ...filterValues, status: e.target.value })}
                    className="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    {STATUS_OPTIONS.map(o => <option key={o.value} value={o.value}>{o.label}</option>)}
                </select>
                <select value={typeFilter} onChange={e => handleFilterChange({ ...filterValues, employment_type: e.target.value })}
                    className="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    {EMPLOYMENT_TYPE_OPTIONS.map(o => <option key={o.value} value={o.value}>{o.label}</option>)}
                </select>
            </div>

            {loading ? (
                <div className="flex justify-center py-20"><Spinner /></div>
            ) : error && typeof error === 'string' ? (
                <div className="rounded-lg bg-red-50 p-4 text-red-700">{error}</div>
            ) : (
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Employee</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Employee #</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Department</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Position</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Type</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                                    <th className="px-4 py-3 text-left font-medium text-gray-600">Hire Date</th>
                                    <th className="px-4 py-3 text-center font-medium text-gray-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {employees.map(emp => (
                                    <tr key={emp.id} className="hover:bg-gray-50 transition-colors">
                                        <td className="px-4 py-3">
                                            <button onClick={() => loadDetail(emp.id)} className="font-medium text-indigo-600 hover:text-indigo-800">
                                                {emp.full_name || `${emp.first_name} ${emp.last_name}`}
                                            </button>
                                            <p className="text-xs text-gray-500">{emp.email}</p>
                                        </td>
                                        <td className="px-4 py-3 text-gray-600">{emp.employee_number}</td>
                                        <td className="px-4 py-3 text-gray-600">{emp.department?.name || '-'}</td>
                                        <td className="px-4 py-3 text-gray-600">{emp.position?.name || '-'}</td>
                                        <td className="px-4 py-3 text-gray-600 capitalize">{emp.employment_type?.replace(/_/g, ' ')}</td>
                                        <td className="px-4 py-3"><StatusBadge status={emp.status} /></td>
                                        <td className="px-4 py-3 text-gray-600">{formatDate(emp.hire_date)}</td>
                                        <td className="px-4 py-3">
                                            <div className="flex items-center justify-center gap-1">
                                                <button onClick={() => loadDetail(emp.id)} className="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100" title="View">
                                                    <Eye className="h-4 w-4" />
                                                </button>
                                                <button onClick={() => openForm(emp)} className="p-1.5 rounded-lg text-blue-500 hover:bg-blue-50" title="Edit">
                                                    <Edit2 className="h-4 w-4" />
                                                </button>
                                                <button onClick={() => handleDelete(emp.id)} className="p-1.5 rounded-lg text-red-500 hover:bg-red-50" title="Delete">
                                                    <Trash2 className="h-4 w-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                                {employees.length === 0 && (
                                    <tr><td colSpan={8} className="px-4 py-12 text-center text-gray-400">No employees found.</td></tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                    {lastPage > 1 && (
                        <div className="flex items-center justify-between border-t border-gray-200 px-4 py-3">
                            <p className="text-sm text-gray-500">Page {currentPage} of {lastPage} ({total} total)</p>
                            <div className="flex gap-1">
                                {Array.from({ length: Math.min(lastPage, 7) }, (_, i) => i + 1).map(page => (
                                    <button key={page} onClick={() => handlePageChange(page)}
                                        className={`px-3 py-1 rounded-lg text-sm ${page === currentPage ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100'}`}>
                                        {page}
                                    </button>
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}

function Field({ label, error, children }) {
    return (
        <div>
            <label className="mb-1 block text-sm font-medium text-gray-700">{label}</label>
            {children}
            {error && <p className="mt-1 text-xs text-red-600">{Array.isArray(error) ? error[0] : error}</p>}
        </div>
    );
}
