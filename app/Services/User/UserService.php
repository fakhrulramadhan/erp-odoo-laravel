<?php

namespace App\Services\User;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15)
    {
        return $this->userRepository->getWithRelations($filters, $perPage);
    }

    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $data['password'] = Hash::make($data['password']);
            $user = $this->userRepository->create($data);

            if (!empty($data['roles'])) {
                $user->syncRoles($data['roles']);
            }

            return $user->load(['company', 'branch', 'department', 'position', 'roles']);
        });
    }

    public function update(int $id, array $data): User
    {
        return DB::transaction(function () use ($id, $data) {
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $user = $this->userRepository->update($id, $data);

            if (isset($data['roles'])) {
                $user->syncRoles($data['roles']);
            }

            return $user->load(['company', 'branch', 'department', 'position', 'roles']);
        });
    }

    public function delete(int $id): bool
    {
        return $this->userRepository->delete($id);
    }

    public function find(int $id): User
    {
        return $this->userRepository->findById($id);
    }
}
