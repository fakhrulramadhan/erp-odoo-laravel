const STATUS_STYLES = {
    draft: 'bg-gray-100 text-gray-700',
    waiting_approval: 'bg-yellow-100 text-yellow-700',
    approved: 'bg-blue-100 text-blue-700',
    ordered: 'bg-indigo-100 text-indigo-700',
    partial_received: 'bg-orange-100 text-orange-700',
    received: 'bg-green-100 text-green-700',
    closed: 'bg-gray-200 text-gray-600',
    cancelled: 'bg-red-100 text-red-700',
    sent: 'bg-blue-100 text-blue-700',
    received_quotation: 'bg-purple-100 text-purple-700',
    accepted: 'bg-green-100 text-green-700',
    rejected: 'bg-red-100 text-red-700',
    waiting: 'bg-yellow-100 text-yellow-700',
    confirmed: 'bg-blue-100 text-blue-700',
    assigned: 'bg-indigo-100 text-indigo-700',
    done: 'bg-green-100 text-green-700',
    validated: 'bg-green-100 text-green-700',
    pending: 'bg-yellow-100 text-yellow-700',
    submitted: 'bg-blue-100 text-blue-700',
    // Phase 4: Manufacturing
    material_reserved: 'bg-teal-100 text-teal-700',
    in_production: 'bg-blue-100 text-blue-700',
    quality_check: 'bg-purple-100 text-purple-700',
    finished: 'bg-green-100 text-green-700',
    active: 'bg-green-100 text-green-700',
    under_review: 'bg-yellow-100 text-yellow-700',
    obsolete: 'bg-gray-200 text-gray-600',
    in_progress: 'bg-blue-100 text-blue-700',
    passed: 'bg-green-100 text-green-700',
    failed: 'bg-red-100 text-red-700',
    requested: 'bg-yellow-100 text-yellow-700',
    scheduled: 'bg-blue-100 text-blue-700',
    completed: 'bg-green-100 text-green-700',
    in_maintenance: 'bg-orange-100 text-orange-700',
    transferred: 'bg-indigo-100 text-indigo-700',
    disposed: 'bg-red-100 text-red-700',
    retired: 'bg-gray-200 text-gray-600',
    // Phase 5: HRM
    on_leave: 'bg-yellow-100 text-yellow-700',
    resigned: 'bg-gray-200 text-gray-600',
    terminated: 'bg-red-100 text-red-700',
    checked_in: 'bg-green-100 text-green-700',
    checked_out: 'bg-gray-200 text-gray-600',
    absent: 'bg-red-100 text-red-700',
    late: 'bg-orange-100 text-orange-700',
    holiday: 'bg-blue-100 text-blue-700',
    pending_approval: 'bg-yellow-100 text-yellow-700',
    paid: 'bg-green-100 text-green-700',
    published: 'bg-blue-100 text-blue-700',
    expired: 'bg-red-100 text-red-700',
    renewed: 'bg-teal-100 text-teal-700',
    computing: 'bg-yellow-100 text-yellow-700',
    computed: 'bg-blue-100 text-blue-700',
    new: 'bg-gray-100 text-gray-700',
    screening: 'bg-blue-100 text-blue-700',
    technical_test: 'bg-yellow-100 text-yellow-700',
    offer: 'bg-green-100 text-green-700',
    hired: 'bg-emerald-100 text-emerald-700',
    freelance: 'bg-purple-100 text-purple-700',
    internship: 'bg-orange-100 text-orange-700',
    full_time: 'bg-green-100 text-green-700',
    part_time: 'bg-blue-100 text-blue-700',
    contract: 'bg-indigo-100 text-indigo-700',
    probation: 'bg-yellow-100 text-yellow-700',
    permanent: 'bg-green-100 text-green-700',
    locked: 'bg-gray-300 text-gray-700',
    open: 'bg-green-100 text-green-700',
    verified: 'bg-blue-100 text-blue-700',
    pass: 'bg-green-100 text-green-700',
    fail: 'bg-red-100 text-red-700',
    on_hold: 'bg-yellow-100 text-yellow-700',
};

export function StatusBadge({ status, label }) {
    const style = STATUS_STYLES[status] || 'bg-gray-100 text-gray-700';
    const displayLabel = label || status?.replace(/_/g, ' ') || 'Unknown';
    return (
        <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize ${style}`}>
            {displayLabel}
        </span>
    );
}

export function formatCurrency(amount, currency = 'IDR') {
    const num = Number(amount) || 0;
    if (currency === 'IDR') {
        return `Rp ${num.toLocaleString('id-ID')}`;
    }
    return `${currency} ${num.toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
}

export function formatDate(dateStr) {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', {
        year: 'numeric', month: 'short', day: 'numeric',
    });
}
