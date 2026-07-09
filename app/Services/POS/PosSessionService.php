<?php

namespace App\Services\POS;

use App\Enums\POSSessionStatus;
use App\Models\PosSession;
use App\Repositories\PosSessionRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PosSessionService
{
    public function __construct(
        protected PosSessionRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): PosSession
    {
        return $this->repository->findWithDetails($id);
    }

    public function open(array $data): PosSession
    {
        return DB::transaction(function () use ($data) {
            $data['session_number'] = $this->repository->getNextSessionNumber();
            $data['status'] = POSSessionStatus::Open;
            $data['opened_at'] = now();
            $data['user_id'] = auth()->id();
            $data['created_by'] = auth()->id();
            return $this->repository->create($data);
        });
    }

    public function close(int $id, array $data): PosSession
    {
        return DB::transaction(function () use ($id, $data) {
            $data['status'] = POSSessionStatus::Closed;
            $data['closed_at'] = now();
            $data['updated_by'] = auth()->id();
            return $this->repository->update($id, $data);
        });
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
