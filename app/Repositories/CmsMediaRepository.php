<?php

namespace App\Repositories;

use App\Models\CmsMedia;
use Illuminate\Database\Eloquent\Builder;

class CmsMediaRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new CmsMedia());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = CmsMedia::with(['uploader', 'company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $query->where('file_name', 'like', "%{$filters['search']}%");
        }

        if (!empty($filters['file_type'])) {
            $query->where('file_type', 'like', "%{$filters['file_type']}%");
        }

        if (!empty($filters['folder'])) {
            $query->where('folder', $filters['folder']);
        }
    }
}
