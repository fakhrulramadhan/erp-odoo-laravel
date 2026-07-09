<?php

namespace App\Services\Purchasing;

use App\Models\PurchaseRequisition;
use App\Repositories\PurchaseRequisitionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class PurchaseRequisitionService
{
    public function __construct(
        protected PurchaseRequisitionRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): PurchaseRequisition
    {
        return $this->repository->findWithLines($id);
    }

    public function create(array $data): PurchaseRequisition
    {
        return DB::transaction(function () use ($data) {
            $lines = $data['lines'] ?? [];
            unset($data['lines']);

            $data['requisition_number'] = $this->repository->getNextRequisitionNumber();
            $data['status'] = 'draft';
            $data['created_by'] = auth()->id();

            $requisition = $this->repository->create($data);

            foreach ($lines as $index => $line) {
                $line['line_number'] = $index + 1;
                $requisition->lines()->create($line);
            }

            return $this->repository->findWithLines($requisition->id);
        });
    }

    public function update(int $id, array $data): PurchaseRequisition
    {
        return DB::transaction(function () use ($id, $data) {
            $requisition = $this->repository->findById($id);

            if (!in_array($requisition->status, ['draft', 'submitted'])) {
                throw new \DomainException('Cannot edit a requisition that is not in draft or submitted status.');
            }

            $lines = $data['lines'] ?? null;
            unset($data['lines']);

            $data['updated_by'] = auth()->id();
            $this->repository->update($id, $data);

            if ($lines !== null) {
                $requisition->lines()->delete();
                foreach ($lines as $index => $line) {
                    $line['line_number'] = $index + 1;
                    $requisition->lines()->create($line);
                }
            }

            return $this->repository->findWithLines($id);
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $requisition = $this->repository->findById($id);
            if ($requisition->status !== 'draft') {
                throw new \DomainException('Can only delete draft requisitions.');
            }
            return $this->repository->delete($id);
        });
    }

    public function approve(int $id): PurchaseRequisition
    {
        return DB::transaction(function () use ($id) {
            $requisition = $this->repository->findById($id);
            $requisition->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
            return $this->repository->findWithLines($id);
        });
    }

    public function cancel(int $id): PurchaseRequisition
    {
        return DB::transaction(function () use ($id) {
            $requisition = $this->repository->findById($id);
            $requisition->update(['status' => 'cancelled']);
            return $this->repository->findWithLines($id);
        });
    }
}
