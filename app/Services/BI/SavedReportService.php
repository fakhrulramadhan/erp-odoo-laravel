<?php

namespace App\Services\BI;

use App\Models\SavedReport;
use App\Repositories\SavedReportRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SavedReportService
{
    public function __construct(
        protected SavedReportRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): SavedReport
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): SavedReport
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = auth()->id();
            return $this->repository->create($data);
        });
    }

    public function update(int $id, array $data): SavedReport
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

    public function execute(int $id): array
    {
        $report = $this->repository->findById($id);
        // TODO: Execute report based on config/query
        return ['report' => $report, 'data' => []];
    }
}
