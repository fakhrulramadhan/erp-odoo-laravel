<?php

namespace App\Services\Security;

use App\Models\LoginHistory;
use App\Repositories\LoginHistoryRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class LoginHistoryService
{
    public function __construct(
        protected LoginHistoryRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): LoginHistory
    {
        return $this->repository->findById($id);
    }

    public function recordLogin(array $data): LoginHistory
    {
        return DB::transaction(function () use ($data) {
            return $this->repository->create($data);
        });
    }
}
