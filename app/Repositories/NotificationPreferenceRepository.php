<?php

namespace App\Repositories;

use App\Models\NotificationPreference;

class NotificationPreferenceRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new NotificationPreference());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = NotificationPreference::with(['user', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }
}
