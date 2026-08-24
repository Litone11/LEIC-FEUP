<?php

namespace App\Policies;

use App\Models\TaskList;
use App\Models\User;

class TaskListPolicy
{
    public function delete(User $user, TaskList $taskList): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($taskList->created_by === $user->user_id) {
            return true;
        }

        $project = $taskList->group?->project;
        if (! $project) {
            return false;
        }

        return $project->roleOf($user->user_id) === 'coordinator';
    }
}
