<?php

namespace App\Services\BI;

use App\Models\Dashboard;
use App\Repositories\DashboardRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function __construct(
        protected DashboardRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): Dashboard
    {
        return $this->repository->findWithWidgets($id);
    }

    public function create(array $data): Dashboard
    {
        return DB::transaction(function () use ($data) {
            $widgets = $data['widgets'] ?? [];
            unset($data['widgets']);

            $data['created_by'] = auth()->id();
            $dashboard = $this->repository->create($data);

            foreach ($widgets as $index => $widget) {
                $widget['sequence'] = $index + 1;
                $dashboard->widgets()->create($widget);
            }

            return $this->repository->findWithWidgets($dashboard->id);
        });
    }

    public function update(int $id, array $data): Dashboard
    {
        return DB::transaction(function () use ($id, $data) {
            $data['updated_by'] = auth()->id();
            return $this->repository->update($id, $data);
        });
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
