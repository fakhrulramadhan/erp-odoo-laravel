<?php

namespace App\Services\Security;

use App\Models\DeviceSession;
use App\Repositories\DeviceSessionRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DeviceSessionService
{
    public function __construct(
        protected DeviceSessionRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): DeviceSession
    {
        return $this->repository->findById($id);
    }

    public function revoke(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            return $this->repository->update($id, [
                'is_revoked' => true,
                'updated_by' => auth()->id(),
            ]) ? true : false;
        });
    }

    public function revokeAllForUser(int $userId): int
    {
        return DB::transaction(function () use ($userId) {
            return DeviceSession::where('user_id', $userId)
                ->where('is_revoked', false)
                ->update([
                    'is_revoked' => true,
                    'updated_by' => auth()->id(),
                ]);
        });
    }
}
