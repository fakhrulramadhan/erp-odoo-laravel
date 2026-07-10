export default function Logo({ size = 'md', showText = true, variant = 'dark', className = '' }) {
    const sizes = {
        sm: { icon: 'h-8 w-8', iconSize: 'h-5 w-5', rounded: 'rounded-lg', textSize: 'text-base' },
        md: { icon: 'h-10 w-10', iconSize: 'h-6 w-6', rounded: 'rounded-xl', textSize: 'text-lg' },
        lg: { icon: 'h-14 w-14', iconSize: 'h-8 w-8', rounded: 'rounded-2xl', textSize: 'text-2xl' },
    };
    const s = sizes[size] || sizes.md;
    const isDark = variant === 'dark';

    return (
        <div className={`flex items-center gap-2.5 ${className}`}>
            <div className={`flex ${s.icon} items-center justify-center ${s.rounded} bg-gradient-to-br from-indigo-600 to-indigo-700 shadow-md shadow-indigo-500/25`}>
                <svg
                    className={s.iconSize}
                    viewBox="0 0 32 32"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    {/* Grid / modules pattern — represents integrated ERP */}
                    <rect x="4" y="4" width="10" height="10" rx="2" fill="white" fillOpacity="0.9" />
                    <rect x="18" y="4" width="10" height="10" rx="2" fill="white" fillOpacity="0.65" />
                    <rect x="4" y="18" width="10" height="10" rx="2" fill="white" fillOpacity="0.65" />
                    <rect x="18" y="18" width="10" height="10" rx="2" fill="white" fillOpacity="0.9" />
                    {/* Connecting lines */}
                    <line x1="14" y1="9" x2="18" y2="9" stroke="white" strokeWidth="1.5" strokeLinecap="round" />
                    <line x1="14" y1="23" x2="18" y2="23" stroke="white" strokeWidth="1.5" strokeLinecap="round" />
                    <line x1="9" y1="14" x2="9" y2="18" stroke="white" strokeWidth="1.5" strokeLinecap="round" />
                    <line x1="23" y1="14" x2="23" y2="18" stroke="white" strokeWidth="1.5" strokeLinecap="round" />
                </svg>
            </div>
            {showText && (
                <div className="flex flex-col leading-none">
                    <span className={`${s.textSize} font-bold tracking-tight ${isDark ? 'text-white' : 'text-gray-900'}`}>FLTech</span>
                    <span className={`text-[10px] font-semibold uppercase tracking-widest ${isDark ? 'text-indigo-300' : 'text-indigo-600'}`}>ERP System</span>
                </div>
            )}
        </div>
    );
}
