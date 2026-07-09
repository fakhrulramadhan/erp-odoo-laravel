<?php

namespace App\Services\Manufacturing;

use App\Enums\BomStatus;
use App\Models\BillOfMaterial;
use App\Models\BomLine;
use App\Models\BomRevision;
use App\Repositories\BillOfMaterialRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class BillOfMaterialService
{
    public function __construct(
        protected BillOfMaterialRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): BillOfMaterial
    {
        return $this->repository->findWithLines($id);
    }

    public function create(array $data): BillOfMaterial
    {
        return DB::transaction(function () use ($data) {
            $lines = $data['lines'] ?? [];
            unset($data['lines']);

            $data['bom_number'] = $this->repository->getNextBomNumber();
            $data['status'] = BomStatus::Draft;
            $data['created_by'] = auth()->id();

            if (!empty($data['is_default'])) {
                BillOfMaterial::where('product_id', $data['product_id'])
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            $bom = $this->repository->create($data);

            foreach ($lines as $index => $line) {
                $line['line_number'] = $index + 1;
                $bom->lines()->create($line);
            }

            $bom->recalculate();

            // Create initial revision
            BomRevision::create([
                'bom_id' => $bom->id,
                'revision_number' => 1,
                'revision_code' => 'R1',
                'description' => 'Initial BOM creation',
                'created_by' => auth()->id(),
            ]);

            return $this->repository->findWithLines($bom->id);
        });
    }

    public function update(int $id, array $data): BillOfMaterial
    {
        return DB::transaction(function () use ($id, $data) {
            $bom = $this->repository->findById($id);

            if (!in_array($bom->status, [BomStatus::Draft, BomStatus::Active])) {
                throw new \DomainException('Cannot edit a BOM that is not in draft or active status.');
            }

            $lines = $data['lines'] ?? null;
            unset($data['lines']);

            $data['updated_by'] = auth()->id();

            if (!empty($data['is_default'])) {
                BillOfMaterial::where('product_id', $data['product_id'] ?? $bom->product_id)
                    ->where('is_default', true)
                    ->where('id', '!=', $id)
                    ->update(['is_default' => false]);
            }

            $this->repository->update($id, $data);

            if ($lines !== null) {
                $bom->lines()->delete();
                foreach ($lines as $index => $line) {
                    $line['line_number'] = $index + 1;
                    $bom->lines()->create($line);
                }
            }

            $bom->fresh()->recalculate();

            // Create revision
            $lastRevision = $bom->revisions()->latest('revision_number')->first();
            $nextRevision = $lastRevision ? $lastRevision->revision_number + 1 : 1;
            BomRevision::create([
                'bom_id' => $bom->id,
                'revision_number' => $nextRevision,
                'revision_code' => 'R' . $nextRevision,
                'description' => 'BOM updated',
                'changes' => $data,
                'created_by' => auth()->id(),
            ]);

            return $this->repository->findWithLines($id);
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $bom = $this->repository->findById($id);
            if (!in_array($bom->status, [BomStatus::Draft, BomStatus::Cancelled])) {
                throw new \DomainException('Cannot delete a BOM that is not in draft or cancelled status.');
            }
            return $this->repository->delete($id);
        });
    }

    public function approve(int $id): BillOfMaterial
    {
        return DB::transaction(function () use ($id) {
            $bom = $this->repository->findById($id);
            $bom->transitionTo(BomStatus::Approved);
            $bom->update([
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
            return $this->repository->findWithLines($id);
        });
    }

    public function activate(int $id): BillOfMaterial
    {
        return DB::transaction(function () use ($id) {
            $bom = $this->repository->findById($id);
            $bom->transitionTo(BomStatus::Active);
            return $this->repository->findWithLines($id);
        });
    }

    public function cancel(int $id): BillOfMaterial
    {
        return DB::transaction(function () use ($id) {
            $bom = $this->repository->findById($id);
            $bom->transitionTo(BomStatus::Cancelled);
            return $this->repository->findWithLines($id);
        });
    }

    public function clone(int $id): BillOfMaterial
    {
        return DB::transaction(function () use ($id) {
            $original = $this->repository->findWithLines($id);

            $newBom = $this->repository->create([
                'company_id' => $original->company_id,
                'bom_number' => $this->repository->getNextBomNumber(),
                'product_id' => $original->product_id,
                'uom_id' => $original->uom_id,
                'version' => $original->version + 1,
                'bom_type' => $original->bom_type,
                'status' => BomStatus::Draft,
                'quantity' => $original->quantity,
                'scrap_percentage' => $original->scrap_percentage,
                'work_center_id' => $original->work_center_id,
                'notes' => $original->notes,
                'created_by' => auth()->id(),
            ]);

            foreach ($original->lines as $line) {
                $newBom->lines()->create([
                    'line_number' => $line->line_number,
                    'product_id' => $line->product_id,
                    'uom_id' => $line->uom_id,
                    'quantity' => $line->quantity,
                    'quantity_formula' => $line->quantity_formula,
                    'scrap_percentage' => $line->scrap_percentage,
                    'notes' => $line->notes,
                ]);
            }

            return $this->repository->findWithLines($newBom->id);
        });
    }
}
