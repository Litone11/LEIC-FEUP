<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\User;

class AdminPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Admin $admin): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Admin $admin): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Admin $admin): bool
    {
        return $user->isAdmin();
    }
}
