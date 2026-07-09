<?php

namespace App\Services\Security;

use App\Models\PasswordPolicy;
use App\Models\TwoFactorSetting;
use App\Repositories\TwoFactorSettingRepository;
use Illuminate\Support\Facades\DB;

class TwoFactorSettingService
{
    public function __construct(
        protected TwoFactorSettingRepository $repository
    ) {}

    public function find(int $id): TwoFactorSetting
    {
        return $this->repository->findById($id);
    }

    public function findByUser(int $userId): ?TwoFactorSetting
    {
        return TwoFactorSetting::where('user_id', $userId)->first();
    }

    public function createOrUpdate(array $data): TwoFactorSetting
    {
        return DB::transaction(function () use ($data) {
            $existing = TwoFactorSetting::where('user_id', $data['user_id'])->first();
            if ($existing) {
                $data['updated_by'] = auth()->id();
                return $this->repository->update($existing->id, $data);
            }
            $data['created_by'] = auth()->id();
            return $this->repository->create($data);
        });
    }

    public function enable(int $userId): TwoFactorSetting
    {
        return $this->createOrUpdate([
            'user_id' => $userId,
            'is_enabled' => true,
        ]);
    }

    public function disable(int $userId): TwoFactorSetting
    {
        return $this->createOrUpdate([
            'user_id' => $userId,
            'is_enabled' => false,
        ]);
    }
}
