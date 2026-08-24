<?php

namespace App\Policies;

use App\Models\Invitation;
use App\Models\User;

class InvitationPolicy
{
    public function view(User $user, Invitation $invitation): bool
    {
        return $user->isAdmin()
            || $invitation->receiver_id === $user->user_id
            || $invitation->sender_id === $user->user_id;
    }

    public function accept(User $user, Invitation $invitation): bool
    {
        return $invitation->receiver_id === $user->user_id;
    }

    public function decline(User $user, Invitation $invitation): bool
    {
        return $invitation->receiver_id === $user->user_id;
    }
}
