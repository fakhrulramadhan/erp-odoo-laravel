<?php

namespace App\Services\Inventory;

use App\Enums\PickingStatus;
use App\Enums\PickingType;
use App\Enums\StockMoveStatus;
use App\Models\StockPicking;
use App\Repositories\StockPickingRepository;
use App\Repositories\StockQuantRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class StockPickingService
{
    public function __construct(
        protected StockPickingRepository $repository,
        protected StockQuantRepository $quantRepository,
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): StockPicking
    {
        return $this->repository->findWithMoves($id);
    }

    public function create(array $data): StockPicking
    {
        return DB::transaction(function () use ($data) {
            $moves = $data['moves'] ?? [];
            unset($data['moves']);

            $data['picking_number'] = $this->repository->getNextPickingNumber($data['picking_type'] ?? 'incoming');
            $data['status'] = PickingStatus::Draft;
            $data['created_by'] = auth()->id();

            $picking = $this->repository->create($data);

            foreach ($moves as $index => $move) {
                $move['move_number'] = 'SM-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
                $move['status'] = StockMoveStatus::Draft;
                $move['created_by'] = auth()->id();
                $picking->moves()->create($move);
            }

            return $this->repository->findWithMoves($picking->id);
        });
    }

    public function update(int $id, array $data): StockPicking
    {
        return DB::transaction(function () use ($id, $data) {
            $picking = $this->repository->findById($id);

            if (!in_array($picking->status, [PickingStatus::Draft, PickingStatus::Waiting])) {
                throw new \DomainException('Cannot edit a picking that is not in draft or waiting status.');
            }

            $moves = $data['moves'] ?? null;
            unset($data['moves']);

            $data['updated_by'] = auth()->id();
            $this->repository->update($id, $data);

            if ($moves !== null) {
                $picking->moves()->delete();
                foreach ($moves as $index => $move) {
                    $move['move_number'] = 'SM-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
                    $move['status'] = StockMoveStatus::Draft;
                    $move['created_by'] = auth()->id();
                    $picking->moves()->create($move);
                }
            }

            return $this->repository->findWithMoves($id);
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $picking = $this->repository->findById($id);
            if (!in_array($picking->status, [PickingStatus::Draft, PickingStatus::Cancelled])) {
                throw new \DomainException('Cannot delete a picking that is not in draft or cancelled status.');
            }
            return $this->repository->delete($id);
        });
    }

    public function confirm(int $id): StockPicking
    {
        return DB::transaction(function () use ($id) {
            $picking = $this->repository->findById($id);
            $picking->transitionTo(PickingStatus::Confirmed);
            return $this->repository->findWithMoves($id);
        });
    }

    public function validate(int $id): StockPicking
    {
        return DB::transaction(function () use ($id) {
            $picking = $this->repository->findWithMoves($id);
            $picking->transitionTo(PickingStatus::Done);
            $picking->update(['effective_date' => now()]);

            foreach ($picking->moves as $move) {
                $move->update(['status' => StockMoveStatus::Done, 'effective_date' => now()]);

                $quant = \App\Models\StockQuant::firstOrCreate(
                    ['product_id' => $move->product_id, 'location_id' => $move->destination_location_id],
                    ['quantity' => 0, 'reserved_quantity' => 0, 'available_quantity' => 0, 'unit_cost' => $move->unit_cost ?? 0]
                );

                if ($picking->picking_type === PickingType::Incoming) {
                    $quant->addQuantity((float) $move->done_qty, (float) $move->unit_cost);
                } elseif ($picking->picking_type === PickingType::Outgoing) {
                    $quant->removeQuantity((float) $move->done_qty);
                }
            }

            return $this->repository->findWithMoves($id);
        });
    }

    public function cancel(int $id): StockPicking
    {
        return DB::transaction(function () use ($id) {
            $picking = $this->repository->findById($id);
            $picking->transitionTo(PickingStatus::Cancelled);
            return $this->repository->findWithMoves($id);
        });
    }
}
