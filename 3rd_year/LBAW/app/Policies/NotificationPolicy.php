<?php

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;

class NotificationPolicy
{
    public function view(User $user, Notification $notification): bool
    {
        return $user->isAdmin() || $notification->receiver_id === $user->user_id;
    }

    public function update(User $user, Notification $notification): bool
    {
        return $user->isAdmin() || $notification->receiver_id === $user->user_id;
    }

    public function delete(User $user, Notification $notification): bool
    {
        return $user->isAdmin() || $notification->receiver_id === $user->user_id;
    }
}
