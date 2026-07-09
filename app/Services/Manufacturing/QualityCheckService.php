<?php

namespace App\Services\Manufacturing;

use App\Enums\QualityCheckStatus;
use App\Models\QualityCheck;
use App\Models\StockQuant;
use App\Repositories\QualityCheckRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class QualityCheckService
{
    public function __construct(
        protected QualityCheckRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): QualityCheck
    {
        return $this->repository->findWithLines($id);
    }

    public function create(array $data): QualityCheck
    {
        return DB::transaction(function () use ($data) {
            $lines = $data['lines'] ?? [];
            unset($data['lines']);

            $data['check_number'] = $this->repository->getNextCheckNumber();
            $data['status'] = QualityCheckStatus::Draft;
            $data['created_by'] = auth()->id();

            $qc = $this->repository->create($data);

            foreach ($lines as $index => $line) {
                $line['line_number'] = $index + 1;
                $qc->lines()->create($line);
            }

            return $this->repository->findWithLines($qc->id);
        });
    }

    public function update(int $id, array $data): QualityCheck
    {
        return DB::transaction(function () use ($id, $data) {
            $qc = $this->repository->findById($id);

            if (!in_array($qc->status, [QualityCheckStatus::Draft])) {
                throw new \DomainException('Cannot edit a quality check that is not in draft status.');
            }

            $lines = $data['lines'] ?? null;
            unset($data['lines']);

            $data['updated_by'] = auth()->id();
            $this->repository->update($id, $data);

            if ($lines !== null) {
                $qc->lines()->delete();
                foreach ($lines as $index => $line) {
                    $line['line_number'] = $index + 1;
                    $qc->lines()->create($line);
                }
            }

            return $this->repository->findWithLines($id);
        });
    }

    public function startInspection(int $id): QualityCheck
    {
        return DB::transaction(function () use ($id) {
            $qc = $this->repository->findById($id);
            $qc->transitionTo(QualityCheckStatus::InProgress);
            $qc->update([
                'inspector_id' => auth()->id(),
                'inspection_date' => now(),
            ]);
            return $this->repository->findWithLines($id);
        });
    }

    public function pass(int $id, array $data = []): QualityCheck
    {
        return DB::transaction(function () use ($id, $data) {
            $qc = $this->repository->findById($id);
            $qc->transitionTo(QualityCheckStatus::Passed);
            $qc->update([
                'result' => 'pass',
                'notes' => $data['notes'] ?? $qc->notes,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // If source is manufacturing order, mark as finished
            if ($qc->source_type === 'App\\Models\\ManufacturingOrder' && $qc->source) {
                $mo = $qc->source;
                if ($mo->status->value === 'quality_check') {
                    $mo->transitionTo(\App\Enums\ManufacturingOrderStatus::Finished);
                }
            }

            return $this->repository->findWithLines($id);
        });
    }

    public function fail(int $id, array $data = []): QualityCheck
    {
        return DB::transaction(function () use ($id, $data) {
            $qc = $this->repository->findById($id);
            $qc->transitionTo(QualityCheckStatus::Failed);
            $qc->update([
                'result' => 'fail',
                'rejected_qty' => $data['rejected_qty'] ?? $qc->quantity,
                'defect_type' => $data['defect_type'] ?? null,
                'corrective_action' => $data['corrective_action'] ?? null,
                'notes' => $data['notes'] ?? $qc->notes,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Move rejected items to quarantine location
            $rejectedQty = (float) $qc->rejected_qty;
            if ($rejectedQty > 0 && $qc->quarantine_location_id) {
                $quant = StockQuant::firstOrCreate(
                    ['product_id' => $qc->product_id, 'location_id' => $qc->quarantine_location_id],
                    ['quantity' => 0, 'reserved_quantity' => 0, 'available_quantity' => 0, 'unit_cost' => 0]
                );
                $quant->addQuantity($rejectedQty, 0);
            }

            return $this->repository->findWithLines($id);
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $qc = $this->repository->findById($id);
            if ($qc->status !== QualityCheckStatus::Draft) {
                throw new \DomainException('Cannot delete a quality check that is not in draft status.');
            }
            return $this->repository->delete($id);
        });
    }
}
