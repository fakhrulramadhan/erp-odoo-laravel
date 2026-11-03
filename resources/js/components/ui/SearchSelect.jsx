import { useState, useEffect, useRef, useCallback } from 'react';
import { ChevronDown, X } from 'lucide-react';

/**
 * Searchable combobox (autocomplete) for selecting a single option.
 *
 * Props:
 *  - value        : string | number  — the selected value (empty string = none)
 *  - onChange     : (value) => void  — called with the selected option value (string)
 *  - options      : [{ value, label }] — synchronous options (optional)
 *  - loadOptions  : async (search) => [{ value, label }] — async loader (optional)
 *  - label        : field label (optional)
 *  - placeholder  : placeholder text when nothing selected (optional)
 *  - error        : validation error string (optional)
 *  - required     : boolean
 *  - disabled     : boolean
 *  - resolveLabel : (value) => string | undefined — resolve display label when
 *                   `value` is set but not present in `options` (edit mode).
 */
export function SearchSelect({
    value,
    onChange,
    options = [],
    loadOptions,
    label,
    placeholder = 'Search...',
    error,
    required = false,
    disabled = false,
    resolveLabel,
    className = '',
}) {
    const [query, setQuery] = useState('');
    const [open, setOpen] = useState(false);
    const [items, setItems] = useState(options);
    const [loading, setLoading] = useState(false);
    const [highlight, setHighlight] = useState(0);
    const containerRef = useRef(null);
    const inputRef = useRef(null);

    const valueStr = value === '' || value == null ? '' : String(value);

    // Keep local items in sync with synchronous options.
    useEffect(() => {
        if (loadOptions) return;
        setItems(options);
    }, [options, loadOptions]);

    // Keep the latest loader in a ref so a changed function identity (e.g. an
    // inline loader recreated on each parent render) does not cancel in-flight
    // requests and leave the list empty.
    const loadOptionsRef = useRef(loadOptions);
    useEffect(() => {
        loadOptionsRef.current = loadOptions;
    }, [loadOptions]);

    // Async load with debounce (re-runs only when the query changes).
    useEffect(() => {
        const loader = loadOptionsRef.current;
        if (!loader) return;
        let cancelled = false;
        setLoading(true);
        const timer = setTimeout(async () => {
            try {
                const loaded = await loader(query);
                if (!cancelled) setItems(loaded || []);
            } catch {
                if (!cancelled) setItems([]);
            } finally {
                if (!cancelled) setLoading(false);
            }
        }, 250);
        return () => {
            cancelled = true;
            clearTimeout(timer);
        };
    }, [query, !!loadOptions]);

    // Close on outside click.
    useEffect(() => {
        if (!open) return;
        const handler = (e) => {
            if (containerRef.current && !containerRef.current.contains(e.target)) {
                setOpen(false);
            }
        };
        document.addEventListener('mousedown', handler);
        return () => document.removeEventListener('mousedown', handler);
    }, [open]);

    const filtered = loadOptions
        ? items
        : items.filter((o) =>
              String(o.label).toLowerCase().includes(query.toLowerCase())
          );

    const selected = items.find((o) => String(o.value) === valueStr);

    const displayLabel =
        selected?.label ??
        (resolveLabel ? resolveLabel(valueStr) : undefined) ??
        '';

    const selectOption = (opt) => {
        onChange(String(opt.value));
        setOpen(false);
        setQuery('');
        setHighlight(0);
    };

    const clear = () => {
        onChange('');
        setOpen(false);
        setQuery('');
        setHighlight(0);
    };

    const handleKeyDown = (e) => {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            setHighlight((h) => Math.min(h + 1, filtered.length - 1));
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            setHighlight((h) => Math.max(h - 1, 0));
        } else if (e.key === 'Enter') {
            if (open && filtered[highlight]) {
                e.preventDefault();
                selectOption(filtered[highlight]);
            }
        } else if (e.key === 'Escape') {
            setOpen(false);
        }
    };

    return (
        <div className={`space-y-1 ${className}`}>
            {label && <label className="block text-sm font-medium text-gray-700">{label}</label>}
            <div ref={containerRef} className="relative">
                <button
                    type="button"
                    onClick={() => {
                        if (disabled) return;
                        setOpen((o) => !o);
                        if (!open) inputRef.current?.focus();
                    }}
                    disabled={disabled}
                    className={`flex w-full items-center justify-between rounded-lg border bg-white px-3 py-2 text-left text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:outline-none disabled:cursor-not-allowed disabled:bg-gray-50 ${
                        error ? 'border-red-500' : 'border-gray-300'
                    }`}
                >
                    {open ? (
                        <input
                            ref={inputRef}
                            autoFocus
                            value={query}
                            onChange={(e) => {
                                setQuery(e.target.value);
                                setHighlight(0);
                            }}
                            onKeyDown={handleKeyDown}
                            placeholder={displayLabel || placeholder}
                            className="w-full bg-transparent text-sm placeholder:text-gray-400 focus:outline-none"
                        />
                    ) : (
                        <span className={`truncate text-sm ${displayLabel ? 'text-gray-900' : 'text-gray-400'}`}>
                            {displayLabel || placeholder}
                        </span>
                    )}
                    <span className="ml-2 flex shrink-0 items-center gap-1">
                        {valueStr && !open ? (
                            <X
                                className="h-4 w-4 cursor-pointer text-gray-400 hover:text-gray-600"
                                onClick={(e) => {
                                    e.stopPropagation();
                                    clear();
                                }}
                            />
                        ) : (
                            <ChevronDown className="h-4 w-4 text-gray-400" />
                        )}
                    </span>
                </button>

                {open && (
                    <ul className="absolute z-20 mt-1 max-h-60 w-full overflow-auto rounded-lg border border-gray-200 bg-white py-1 shadow-lg">
                        {loading ? (
                            <li className="px-3 py-2 text-sm text-gray-400">Loading...</li>
                        ) : filtered.length === 0 ? (
                            <li className="px-3 py-2 text-sm text-gray-400">No results</li>
                        ) : (
                            filtered.map((opt, idx) => (
                                <li
                                    key={opt.value}
                                    onMouseDown={(e) => {
                                        e.preventDefault();
                                        selectOption(opt);
                                    }}
                                    onMouseEnter={() => setHighlight(idx)}
                                    className={`cursor-pointer px-3 py-2 text-sm ${
                                        idx === highlight ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700'
                                    }`}
                                >
                                    {opt.label}
                                </li>
                            ))
                        )}
                    </ul>
                )}
            </div>
            {error && <p className="text-sm text-red-600">{error}</p>}
        </div>
    );
}

export default SearchSelect;
