export function Badge({ children, variant = 'default', className = '' }) {
    const variants = {
        default: 'bg-gray-100 text-gray-700',
        primary: 'bg-indigo-100 text-indigo-700',
        success: 'bg-green-100 text-green-700',
        warning: 'bg-yellow-100 text-yellow-700',
        danger: 'bg-red-100 text-red-700',
    };

    return (
        <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${variants[variant]} ${className}`}>
            {children}
        </span>
    );
}

export function StatusBadge({ status }) {
    const map = {
        active: { label: 'Active', variant: 'success' },
        inactive: { label: 'Inactive', variant: 'default' },
        suspended: { label: 'Suspended', variant: 'danger' },
    };
    const s = map[status] || { label: status, variant: 'default' };
    return <Badge variant={s.variant}>{s.label}</Badge>;
}
