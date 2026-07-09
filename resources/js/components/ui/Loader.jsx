import { Loader2 } from 'lucide-react';

export function Spinner({ size = 'md', className = '' }) {
    const sizes = { sm: 'h-4 w-4', md: 'h-6 w-6', lg: 'h-8 w-8' };
    return <Loader2 className={`animate-spin text-indigo-600 ${sizes[size]} ${className}`} />;
}

export function PageLoader() {
    return (
        <div className="flex items-center justify-center min-h-screen">
            <div className="text-center">
                <Spinner size="lg" />
                <p className="mt-3 text-sm text-gray-500">Loading...</p>
            </div>
        </div>
    );
}
