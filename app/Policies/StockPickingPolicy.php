<?php

namespace App\Policies;

use App\Models\User;
use App\Models\StockPicking;

class StockPickingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view inventory');
    }

    public function view(User $user, StockPicking $picking): bool
    {
        return $user->can('view inventory');
    }

    public function create(User $user): bool
    {
        return $user->can('manage inventory');
    }

    public function update(User $user, StockPicking $picking): bool
    {
        return $user->can('manage inventory');
    }

    public function delete(User $user, StockPicking $picking): bool
    {
        return $user->can('delete inventory');
    }

    public function validate(User $user, StockPicking $picking): bool
    {
        return $user->can('validate inventory');
    }
}
