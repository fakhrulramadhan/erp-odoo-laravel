<?php

namespace App\Repositories;

use App\Models\EcommerceSetting;

class EcommerceSettingRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new EcommerceSetting());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = EcommerceSetting::with(['company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findByCompany(int $companyId): ?EcommerceSetting
    {
        return EcommerceSetting::where('company_id', $companyId)->first();
    }
}
