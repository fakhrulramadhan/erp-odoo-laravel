<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = [], ?int $perPage = 15): LengthAwarePaginator|Collection
    {
        $query = $this->model->newQuery();

        $this->applyFilters($query, $filters);

        if ($perPage) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    public function findById(int|string $id): ?Model
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int|string $id, array $data): Model
    {
        $model = $this->findById($id);
        $model->update($data);
        return $model->fresh();
    }

    public function delete(int|string $id): bool
    {
        return $this->findById($id)->delete();
    }

    public function restore(int|string $id): bool
    {
        return $this->model->withTrashed()->findOrFail($id)->restore();
    }

    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '') continue;

            if (is_callable($value)) {
                $value($query);
            } else {
                $query->where($key, $value);
            }
        }
    }
}
