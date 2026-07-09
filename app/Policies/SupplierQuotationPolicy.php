<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SupplierQuotation;

class SupplierQuotationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view purchase orders');
    }

    public function view(User $user, SupplierQuotation $quotation): bool
    {
        return $user->can('view purchase orders');
    }

    public function create(User $user): bool
    {
        return $user->can('create purchase orders');
    }

    public function update(User $user, SupplierQuotation $quotation): bool
    {
        return $user->can('edit purchase orders');
    }

    public function delete(User $user, SupplierQuotation $quotation): bool
    {
        return $user->can('delete purchase orders');
    }
}
