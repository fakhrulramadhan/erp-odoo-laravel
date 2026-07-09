export function Input({ label, error, className = '', ...props }) {
    return (
        <div className="space-y-1">
            {label && <label className="block text-sm font-medium text-gray-700">{label}</label>}
            <input
                className={`block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:outline-none ${error ? 'border-red-500' : ''} ${className}`}
                {...props}
            />
            {error && <p className="text-sm text-red-600">{error}</p>}
        </div>
    );
}

export function Select({ label, error, children, className = '', ...props }) {
    return (
        <div className="space-y-1">
            {label && <label className="block text-sm font-medium text-gray-700">{label}</label>}
            <select
                className={`block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:outline-none ${error ? 'border-red-500' : ''} ${className}`}
                {...props}
            >
                {children}
            </select>
            {error && <p className="text-sm text-red-600">{error}</p>}
        </div>
    );
}

export function Textarea({ label, error, className = '', ...props }) {
    return (
        <div className="space-y-1">
            {label && <label className="block text-sm font-medium text-gray-700">{label}</label>}
            <textarea
                className={`block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:outline-none ${error ? 'border-red-500' : ''} ${className}`}
                rows={3}
                {...props}
            />
            {error && <p className="text-sm text-red-600">{error}</p>}
        </div>
    );
}
