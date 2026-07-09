import { AlertCircle, CheckCircle, Info, AlertTriangle, X } from 'lucide-react';

const icons = {
    info: Info,
    success: CheckCircle,
    warning: AlertTriangle,
    error: AlertCircle,
};

const styles = {
    info: 'bg-blue-50 text-blue-800 border-blue-200',
    success: 'bg-green-50 text-green-800 border-green-200',
    warning: 'bg-yellow-50 text-yellow-800 border-yellow-200',
    error: 'bg-red-50 text-red-800 border-red-200',
};

export function Alert({ type = 'info', title, children, onClose }) {
    const Icon = icons[type];

    return (
        <div className={`flex items-start gap-3 rounded-lg border p-4 ${styles[type]}`}>
            <Icon className="mt-0.5 h-5 w-5 shrink-0" />
            <div className="flex-1">
                {title && <h3 className="text-sm font-semibold">{title}</h3>}
                {children && <p className="text-sm mt-1">{children}</p>}
            </div>
            {onClose && (
                <button onClick={onClose} className="shrink-0 hover:opacity-70">
                    <X className="h-4 w-4" />
                </button>
            )}
        </div>
    );
}
