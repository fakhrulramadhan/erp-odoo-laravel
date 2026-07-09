<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new User());
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        $query = User::with(['company', 'branch', 'department', 'position', 'roles']);

        $this->applyFilters($query, $filters);

        return $query->paginate($perPage);
    }

    public function getActiveUsers(?int $companyId = null): Collection
    {
        return User::active()
            ->when($companyId, fn($q) => $q->byCompany($companyId))
            ->get();
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['role'])) {
            $query->role($filters['role']);
        }
    }
}
