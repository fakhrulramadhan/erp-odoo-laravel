<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PurchaseOrder;

class PurchaseOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view purchase orders');
    }

    public function view(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->can('view purchase orders');
    }

    public function create(User $user): bool
    {
        return $user->can('create purchase orders');
    }

    public function update(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->can('edit purchase orders');
    }

    public function delete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->can('delete purchase orders');
    }

    public function approve(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->can('approve purchase orders');
    }

    public function cancel(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->can('cancel purchase orders');
    }
}
