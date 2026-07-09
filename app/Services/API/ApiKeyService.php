<?php

namespace App\Services\API;

use App\Models\ApiKey;
use App\Repositories\ApiKeyRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApiKeyService
{
    public function __construct(
        protected ApiKeyRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): ApiKey
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): ApiKey
    {
        return DB::transaction(function () use ($data) {
            $data['key'] = Str::random(40);
            $data['created_by'] = auth()->id();
            return $this->repository->create($data);
        });
    }

    public function update(int $id, array $data): ApiKey
    {
        return DB::transaction(function () use ($id, $data) {
            $data['updated_by'] = auth()->id();
            return $this->repository->update($id, $data);
        });
    }

    public function revoke(int $id): ApiKey
    {
        return DB::transaction(function () use ($id) {
            return $this->repository->update($id, [
                'is_active' => false,
                'updated_by' => auth()->id(),
            ]);
        });
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
