import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight } from 'lucide-react';

export function Pagination({ currentPage, lastPage, total, perPage, onPageChange }) {
    if (lastPage <= 1) return null;

    const from = (currentPage - 1) * perPage + 1;
    const to = Math.min(currentPage * perPage, total);

    // Generate page numbers with ellipsis
    const getPageNumbers = () => {
        const pages = [];
        const delta = 2;

        for (let i = 1; i <= lastPage; i++) {
            if (
                i === 1 ||
                i === lastPage ||
                (i >= currentPage - delta && i <= currentPage + delta)
            ) {
                pages.push(i);
            } else if (pages[pages.length - 1] !== '...') {
                pages.push('...');
            }
        }
        return pages;
    };

    return (
        <div className="flex items-center justify-between px-4 py-3 border-t border-gray-200 bg-white rounded-b-xl">
            {/* Info */}
            <p className="text-sm text-gray-600">
                Showing <span className="font-medium">{from}</span> to{' '}
                <span className="font-medium">{to}</span> of{' '}
                <span className="font-medium">{total}</span> results
            </p>

            {/* Page Buttons */}
            <div className="flex items-center gap-1">
                {/* First */}
                <button
                    onClick={() => onPageChange(1)}
                    disabled={currentPage === 1}
                    className="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed"
                >
                    <ChevronsLeft className="h-4 w-4" />
                </button>
                {/* Prev */}
                <button
                    onClick={() => onPageChange(currentPage - 1)}
                    disabled={currentPage === 1}
                    className="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed"
                >
                    <ChevronLeft className="h-4 w-4" />
                </button>

                {getPageNumbers().map((page, idx) =>
                    page === '...' ? (
                        <span key={`dots-${idx}`} className="px-2 py-1.5 text-sm text-gray-400">…</span>
                    ) : (
                        <button
                            key={page}
                            onClick={() => onPageChange(page)}
                            className={`min-w-[32px] px-2 py-1.5 text-sm rounded-lg font-medium transition-colors ${
                                page === currentPage
                                    ? 'bg-indigo-600 text-white'
                                    : 'text-gray-600 hover:bg-gray-100'
                            }`}
                        >
                            {page}
                        </button>
                    )
                )}

                {/* Next */}
                <button
                    onClick={() => onPageChange(currentPage + 1)}
                    disabled={currentPage === lastPage}
                    className="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed"
                >
                    <ChevronRight className="h-4 w-4" />
                </button>
                {/* Last */}
                <button
                    onClick={() => onPageChange(lastPage)}
                    disabled={currentPage === lastPage}
                    className="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed"
                >
                    <ChevronsRight className="h-4 w-4" />
                </button>
            </div>
        </div>
    );
}
