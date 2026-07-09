<?php

namespace App\Services\API;

use App\Models\Webhook;
use App\Repositories\WebhookRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class WebhookService
{
    public function __construct(
        protected WebhookRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): Webhook
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): Webhook
    {
        return DB::transaction(function () use ($data) {
            $data['status'] = $data['status'] ?? \App\Enums\WebhookStatus::Active;
            $data['secret'] = $data['secret'] ?? \Illuminate\Support\Str::random(32);
            $data['created_by'] = auth()->id();
            return $this->repository->create($data);
        });
    }

    public function update(int $id, array $data): Webhook
    {
        return DB::transaction(function () use ($id, $data) {
            $data['updated_by'] = auth()->id();
            return $this->repository->update($id, $data);
        });
    }

    public function toggle(int $id): Webhook
    {
        return DB::transaction(function () use ($id) {
            $webhook = $this->repository->findById($id);
            $newStatus = $webhook->status === \App\Enums\WebhookStatus::Active
                ? \App\Enums\WebhookStatus::Inactive
                : \App\Enums\WebhookStatus::Active;
            return $this->repository->update($id, [
                'status' => $newStatus,
                'updated_by' => auth()->id(),
            ]);
        });
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
