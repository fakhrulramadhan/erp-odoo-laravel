<?php

namespace App\Repositories;

use App\Models\NotificationTemplate;

class NotificationTemplateRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new NotificationTemplate());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = NotificationTemplate::with(['company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }
}
