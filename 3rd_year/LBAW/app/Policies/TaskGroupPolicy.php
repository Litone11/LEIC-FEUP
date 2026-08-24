<?php

namespace App\Policies;

use App\Models\TaskGroup;
use App\Models\User;

class TaskGroupPolicy
{
    public function delete(User $user, TaskGroup $taskGroup): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($taskGroup->created_by === $user->user_id) {
            return true;
        }

        $project = $taskGroup->project;
        if (! $project) {
            return false;
        }

        return $project->roleOf($user->user_id) === 'coordinator';
    }
}
