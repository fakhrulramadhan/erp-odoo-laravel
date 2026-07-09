<?php

namespace App\Services\Manufacturing;

use App\Models\Routing;
use App\Repositories\RoutingRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class RoutingService
{
    public function __construct(
        protected RoutingRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): Routing
    {
        return $this->repository->findWithOperations($id);
    }

    public function create(array $data): Routing
    {
        return DB::transaction(function () use ($data) {
            $operations = $data['operations'] ?? [];
            unset($data['operations']);

            $data['routing_number'] = $data['routing_number'] ?? $this->repository->getNextRoutingNumber();
            $data['created_by'] = auth()->id();

            $routing = $this->repository->create($data);

            foreach ($operations as $index => $operation) {
                $operation['sequence'] = $index + 1;
                $routing->operations()->create($operation);
            }

            return $this->repository->findWithOperations($routing->id);
        });
    }

    public function update(int $id, array $data): Routing
    {
        return DB::transaction(function () use ($id, $data) {
            $operations = $data['operations'] ?? null;
            unset($data['operations']);

            $data['updated_by'] = auth()->id();
            $this->repository->update($id, $data);

            if ($operations !== null) {
                $routing = $this->repository->findById($id);
                $routing->operations()->delete();
                foreach ($operations as $index => $operation) {
                    $operation['sequence'] = $index + 1;
                    $routing->operations()->create($operation);
                }
            }

            return $this->repository->findWithOperations($id);
        });
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
