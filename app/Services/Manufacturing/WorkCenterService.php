<?php

namespace App\Services\Manufacturing;

use App\Models\WorkCenter;
use App\Repositories\WorkCenterRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class WorkCenterService
{
    public function __construct(
        protected WorkCenterRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): WorkCenter
    {
        return $this->repository->findWithRelations($id);
    }

    public function create(array $data): WorkCenter
    {
        return DB::transaction(function () use ($data) {
            $operators = $data['operators'] ?? [];
            unset($data['operators']);

            $data['code'] = $data['code'] ?? $this->repository->getNextCode();
            $data['created_by'] = auth()->id();

            $wc = $this->repository->create($data);

            foreach ($operators as $userId) {
                $wc->operators()->attach($userId, ['role' => 'operator', 'is_active' => true]);
            }

            return $this->repository->findWithRelations($wc->id);
        });
    }

    public function update(int $id, array $data): WorkCenter
    {
        return DB::transaction(function () use ($id, $data) {
            $operators = $data['operators'] ?? null;
            unset($data['operators']);

            $data['updated_by'] = auth()->id();
            $this->repository->update($id, $data);

            if ($operators !== null) {
                $wc = $this->repository->findById($id);
                $wc->operators()->detach();
                foreach ($operators as $userId) {
                    $wc->operators()->attach($userId, ['role' => 'operator', 'is_active' => true]);
                }
            }

            return $this->repository->findWithRelations($id);
        });
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
