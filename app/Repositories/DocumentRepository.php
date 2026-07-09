<?php

namespace App\Repositories;

use App\Models\Document;
use Illuminate\Database\Eloquent\Builder;

class DocumentRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Document());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = Document::with(['folder', 'owner', 'company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findWithDetails(int $id): ?Document
    {
        return Document::with([
            'folder', 'owner', 'company', 'versions.uploader', 'shares', 'creator',
        ])->findOrFail($id);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $query->where('title', 'like', "%{$filters['search']}%")
                  ->orWhere('description', 'like', "%{$filters['search']}%");
        }

        if (!empty($filters['folder_id'])) {
            $query->where('folder_id', $filters['folder_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }
}
