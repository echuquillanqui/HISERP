<?php

namespace App\Policies;

use App\Models\Agreement;
use App\Models\User;

class AgreementPolicy
{
    private function canManage(User $user): bool
    {
        return (bool) $user->status && in_array($user->role, [
            'superadmin',
            'administracion',
            'medicina',
            'laboratorio',
        ], true);
    }

    public function viewAny(User $user): bool
    {
        return $this->canManage($user);
    }

    public function view(User $user, Agreement $agreement): bool
    {
        return $this->canManage($user);
    }

    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    public function update(User $user, Agreement $agreement): bool
    {
        return $this->canManage($user);
    }

    public function delete(User $user, Agreement $agreement): bool
    {
        return $this->canManage($user);
    }
}
