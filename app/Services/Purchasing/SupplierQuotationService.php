<?php

namespace App\Services\Purchasing;

use App\Enums\QuotationStatus;
use App\Models\SupplierQuotation;
use App\Repositories\SupplierQuotationRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class SupplierQuotationService
{
    public function __construct(
        protected SupplierQuotationRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): SupplierQuotation
    {
        return $this->repository->findWithLines($id);
    }

    public function create(array $data): SupplierQuotation
    {
        return DB::transaction(function () use ($data) {
            $lines = $data['lines'] ?? [];
            unset($data['lines']);

            $data['quotation_number'] = $this->repository->getNextQuotationNumber();
            $data['status'] = QuotationStatus::Draft;
            $data['created_by'] = auth()->id();

            $quotation = $this->repository->create($data);

            foreach ($lines as $index => $line) {
                $line['line_number'] = $index + 1;
                $quotation->lines()->create($line);
            }

            $quotation->recalculate();

            return $this->repository->findWithLines($quotation->id);
        });
    }

    public function update(int $id, array $data): SupplierQuotation
    {
        return DB::transaction(function () use ($id, $data) {
            $quotation = $this->repository->findById($id);

            if (!in_array($quotation->status, [QuotationStatus::Draft, QuotationStatus::Sent])) {
                throw new \DomainException('Cannot edit a quotation that is not in draft or sent status.');
            }

            $lines = $data['lines'] ?? null;
            unset($data['lines']);

            $data['updated_by'] = auth()->id();
            $this->repository->update($id, $data);

            if ($lines !== null) {
                $quotation->lines()->delete();
                foreach ($lines as $index => $line) {
                    $line['line_number'] = $index + 1;
                    $quotation->lines()->create($line);
                }
            }

            $quotation->fresh()->recalculate();

            return $this->repository->findWithLines($id);
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $quotation = $this->repository->findById($id);
            if ($quotation->status !== QuotationStatus::Draft) {
                throw new \DomainException('Can only delete draft quotations.');
            }
            return $this->repository->delete($id);
        });
    }

    public function sendToVendor(int $id): SupplierQuotation
    {
        return DB::transaction(function () use ($id) {
            $quotation = $this->repository->findById($id);
            $quotation->transitionTo(QuotationStatus::Sent);
            return $this->repository->findWithLines($id);
        });
    }

    public function markAsAccepted(int $id): SupplierQuotation
    {
        return DB::transaction(function () use ($id) {
            $quotation = $this->repository->findById($id);
            $quotation->transitionTo(QuotationStatus::Accepted);
            return $this->repository->findWithLines($id);
        });
    }

    public function markAsRejected(int $id): SupplierQuotation
    {
        return DB::transaction(function () use ($id) {
            $quotation = $this->repository->findById($id);
            $quotation->transitionTo(QuotationStatus::Rejected);
            return $this->repository->findWithLines($id);
        });
    }
}
