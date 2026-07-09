export function Card({ title, subtitle, actions, children, className = '' }) {
    return (
        <div className={`bg-white rounded-xl border border-gray-200 shadow-sm ${className}`}>
            {(title || actions) && (
                <div className="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                    <div>
                        {title && <h3 className="text-lg font-semibold text-gray-900">{title}</h3>}
                        {subtitle && <p className="text-sm text-gray-500 mt-0.5">{subtitle}</p>}
                    </div>
                    {actions && <div className="flex items-center gap-2">{actions}</div>}
                </div>
            )}
            <div className="p-6">{children}</div>
        </div>
    );
}

export function StatCard({ label, value, icon: Icon, color = 'indigo', change }) {
    const colors = {
        indigo: 'bg-indigo-50 text-indigo-600',
        green: 'bg-green-50 text-green-600',
        yellow: 'bg-yellow-50 text-yellow-600',
        red: 'bg-red-50 text-red-600',
        blue: 'bg-blue-50 text-blue-600',
    };

    return (
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <div className="flex items-center justify-between">
                <div>
                    <p className="text-sm font-medium text-gray-500">{label}</p>
                    <p className="text-2xl font-bold text-gray-900 mt-1">{value}</p>
                    {change !== undefined && (
                        <p className={`text-xs mt-1 ${change >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                            {change >= 0 ? '↑' : '↓'} {Math.abs(change)}%
                        </p>
                    )}
                </div>
                {Icon && (
                    <div className={`rounded-lg p-3 ${colors[color]}`}>
                        <Icon className="h-6 w-6" />
                    </div>
                )}
            </div>
        </div>
    );
}
