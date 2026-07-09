<?php

namespace App\Services\Document;

use App\Models\Document;
use App\Repositories\DocumentRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DocumentService
{
    public function __construct(
        protected DocumentRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): Document
    {
        return $this->repository->findWithDetails($id);
    }

    public function create(array $data): Document
    {
        return DB::transaction(function () use ($data) {
            $data['owner_id'] = $data['owner_id'] ?? auth()->id();
            $data['status'] = $data['status'] ?? \App\Enums\DocumentStatus::Draft;
            $data['created_by'] = auth()->id();
            return $this->repository->create($data);
        });
    }

    public function update(int $id, array $data): Document
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

    public function addVersion(int $id, array $data): Document
    {
        return DB::transaction(function () use ($id, $data) {
            $doc = $this->repository->findById($id);
            $lastVersion = $doc->versions()->max('version_number') ?? 0;
            $data['version_number'] = $lastVersion + 1;
            $data['uploaded_by'] = auth()->id();
            $doc->versions()->create($data);
            return $this->repository->findWithDetails($id);
        });
    }
}
