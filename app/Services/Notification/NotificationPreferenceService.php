<?php

namespace App\Services\Notification;

use App\Models\NotificationPreference;
use App\Repositories\NotificationPreferenceRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class NotificationPreferenceService
{
    public function __construct(
        protected NotificationPreferenceRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): NotificationPreference
    {
        return $this->repository->findById($id);
    }

    public function findByUser(int $userId): ?NotificationPreference
    {
        return NotificationPreference::where('user_id', $userId)->first();
    }

    public function createOrUpdate(array $data): NotificationPreference
    {
        return DB::transaction(function () use ($data) {
            $existing = NotificationPreference::where('user_id', $data['user_id'])->first();
            if ($existing) {
                $data['updated_by'] = auth()->id();
                return $this->repository->update($existing->id, $data);
            }
            $data['created_by'] = auth()->id();
            return $this->repository->create($data);
        });
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
