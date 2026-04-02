<?php

namespace App\Policies;

use App\Models\Agreement;
use App\Models\User;

class AgreementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->status === true;
    }

    public function view(User $user, Agreement $agreement): bool
    {
        return $user->status === true;
    }

    public function create(User $user): bool
    {
        return $user->role === 'superadmin';
    }

    public function update(User $user, Agreement $agreement): bool
    {
        return in_array($user->role, ['superadmin', 'administracion'], true);
    }

    public function delete(User $user, Agreement $agreement): bool
    {
        return in_array($user->role, ['superadmin', 'administracion'], true);
    }
}
