<?php

namespace App\Policies;

use App\Models\User;
use App\Models\InventoryAdjustment;

class InventoryAdjustmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view inventory');
    }

    public function view(User $user, InventoryAdjustment $adjustment): bool
    {
        return $user->can('view inventory');
    }

    public function create(User $user): bool
    {
        return $user->can('manage inventory');
    }

    public function update(User $user, InventoryAdjustment $adjustment): bool
    {
        return $user->can('manage inventory');
    }

    public function delete(User $user, InventoryAdjustment $adjustment): bool
    {
        return $user->can('delete inventory');
    }

    public function validate(User $user, InventoryAdjustment $adjustment): bool
    {
        return $user->can('validate inventory');
    }
}
