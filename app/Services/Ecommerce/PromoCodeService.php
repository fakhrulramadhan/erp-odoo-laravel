<?php

namespace App\Services\Ecommerce;

use App\Models\PromoCode;
use App\Repositories\PromoCodeRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PromoCodeService
{
    public function __construct(
        protected PromoCodeRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): PromoCode
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): PromoCode
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = auth()->id();
            return $this->repository->create($data);
        });
    }

    public function update(int $id, array $data): PromoCode
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

    public function validate(string $code, float $orderAmount): ?PromoCode
    {
        $promo = PromoCode::where('code', $code)
            ->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        if (!$promo) return null;
        if ($promo->usage_limit && $promo->used_count >= $promo->usage_limit) return null;
        if ($promo->min_order_amount && $orderAmount < $promo->min_order_amount) return null;

        return $promo;
    }
}
