import { Search, Filter, X } from 'lucide-react';

export function SearchFilter({
    search = '',
    onSearchChange,
    searchPlaceholder = 'Search...',
    filters = [],
    filterValues = {},
    onFilterChange,
    actions,
}) {
    const hasActiveFilters = Object.values(filterValues).some(v => v !== '' && v !== undefined && v !== null);

    const clearFilters = () => {
        const cleared = {};
        filters.forEach(f => { cleared[f.name] = ''; });
        onFilterChange(cleared);
        onSearchChange('');
    };

    return (
        <div className="bg-white rounded-xl border border-gray-200 p-4 mb-4">
            <div className="flex flex-wrap items-center gap-3">
                {/* Search Input */}
                <div className="relative flex-1 min-w-[240px]">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <input
                        type="text"
                        value={search}
                        onChange={(e) => onSearchChange(e.target.value)}
                        placeholder={searchPlaceholder}
                        className="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                    />
                    {search && (
                        <button
                            onClick={() => onSearchChange('')}
                            className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        >
                            <X className="h-4 w-4" />
                        </button>
                    )}
                </div>

                {/* Filter Dropdowns */}
                {filters.map((filter) => (
                    <select
                        key={filter.name}
                        value={filterValues[filter.name] ?? ''}
                        onChange={(e) => onFilterChange({ ...filterValues, [filter.name]: e.target.value })}
                        className="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white"
                    >
                        <option value="">{filter.label}</option>
                        {filter.options.map((opt) => (
                            <option key={opt.value} value={opt.value}>{opt.label}</option>
                        ))}
                    </select>
                ))}

                {/* Clear Filters */}
                {hasActiveFilters && (
                    <button
                        onClick={clearFilters}
                        className="flex items-center gap-1 px-3 py-2 text-sm text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors"
                    >
                        <X className="h-4 w-4" />
                        Clear
                    </button>
                )}

                {/* Custom Actions */}
                {actions && <div className="flex items-center gap-2 ml-auto">{actions}</div>}
            </div>
        </div>
    );
}
