import { useState } from 'react';
import { ArrowUpDown, ArrowUp, ArrowDown } from 'lucide-react';
import { Badge, StatusBadge } from './Badge';

function CellRenderer({ type, value, row }) {
    if (type === 'badge') {
        return <Badge variant={value?.variant || 'default'}>{value?.label ?? value}</Badge>;
    }
    if (type === 'status') {
        return <StatusBadge status={value} />;
    }
    if (type === 'boolean') {
        return value
            ? <Badge variant="success">Yes</Badge>
            : <Badge variant="default">No</Badge>;
    }
    if (type === 'date') {
        return value ? new Date(value).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '-';
    }
    if (type === 'datetime') {
        return value ? new Date(value).toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-';
    }
    if (type === 'number') {
        return typeof value === 'number' ? value.toLocaleString() : value ?? '-';
    }
    if (type === 'currency') {
        return value != null ? `${row?.currency_symbol || ''}${Number(value).toLocaleString(undefined, { minimumFractionDigits: 2 })}` : '-';
    }
    return value ?? '-';
}

export function DataTable({ columns, data, loading, emptyMessage = 'No data found', onSort, sortKey, sortDir }) {
    const rows = Array.isArray(data) ? data : [];
    const handleSort = (col) => {
        if (!col.sortable || !onSort) return;
        const newDir = sortKey === col.key && sortDir === 'asc' ? 'desc' : 'asc';
        onSort(col.key, newDir);
    };

    if (loading) {
        return (
            <div className="animate-pulse">
                <div className="h-10 bg-gray-200 rounded-t-lg" />
                {[1, 2, 3, 4, 5].map((i) => (
                    <div key={i} className="h-12 bg-gray-100 border-b" />
                ))}
            </div>
        );
    }

    return (
        <div className="overflow-x-auto rounded-lg border border-gray-200">
            <table className="min-w-full divide-y divide-gray-200">
                <thead className="bg-gray-50">
                    <tr>
                        {columns.map((col) => (
                            <th
                                key={col.key}
                                className={`px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 ${col.sortable ? 'cursor-pointer select-none hover:text-gray-700' : ''}`}
                                style={col.width ? { width: col.width } : {}}
                                onClick={() => handleSort(col)}
                            >
                                <div className="flex items-center gap-1">
                                    {col.label}
                                    {col.sortable && (
                                        sortKey === col.key ? (
                                            sortDir === 'asc' ? <ArrowUp className="h-3 w-3" /> : <ArrowDown className="h-3 w-3" />
                                        ) : (
                                            <ArrowUpDown className="h-3 w-3 opacity-30" />
                                        )
                                    )}
                                </div>
                            </th>
                        ))}
                    </tr>
                </thead>
                <tbody className="divide-y divide-gray-200 bg-white">
                    {rows.length === 0 ? (
                        <tr>
                            <td colSpan={columns.length} className="px-4 py-8 text-center text-sm text-gray-500">
                                {emptyMessage}
                            </td>
                        </tr>
                    ) : (
                        rows.map((row, idx) => (
                            <tr key={row.id || idx} className="hover:bg-gray-50 transition-colors">
                                {columns.map((col) => (
                                    <td key={col.key} className="px-4 py-3 text-sm text-gray-700">
                                        {col.render
                                            ? col.render(row)
                                            : col.type
                                                ? <CellRenderer type={col.type} value={row[col.key]} row={row} />
                                                : row[col.key] ?? '-'
                                        }
                                    </td>
                                ))}
                            </tr>
                        ))
                    )}
                </tbody>
            </table>
        </div>
    );
}
