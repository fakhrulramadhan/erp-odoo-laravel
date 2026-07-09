<?php

namespace App\Repositories;

use App\Models\DocumentFolder;

class DocumentFolderRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new DocumentFolder());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = DocumentFolder::with(['parent', 'children', 'company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }
}
