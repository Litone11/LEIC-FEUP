<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Any member of the project can view the task details
        if ($task->project?->hasMember($user->user_id)) {
            return true;
        }

        // Fallback to tighter check (coordinator/responsible/assignee)
        $role = $task->roleOf($user->user_id);
        return $role === 'coordinator'
            || $task->task_responsible_id === $user->user_id
            || $task->assignee_id === $user->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Project $project): bool
    {
        return $user->isAdmin() || $project->hasMember($user->user_id);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {

        $role = $task->roleOf($user->user_id);

        return $role === 'coordinator'
        || $task->task_responsible_id === $user->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        //Not sure if TR's could delete
        $role = $task->roleOf($user->user_id);

        return $role === 'coordinator'
            || $task->task_responsible_id === $user->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        return false;
    }

    public function manageDependencies(User $user, Task $task): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $role = $task->roleOf($user->user_id);

        return $role === 'coordinator'
            || $task->task_responsible_id === $user->user_id;
    }
}
