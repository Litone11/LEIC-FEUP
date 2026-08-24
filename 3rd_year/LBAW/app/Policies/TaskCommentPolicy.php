<?php

namespace App\Policies;

use App\Models\TaskComment;
use App\Models\User;

class TaskCommentPolicy
{
    public function delete(User $user, TaskComment $comment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($comment->user_id === $user->user_id) {
            return true;
        }

        $task = $comment->thread?->task;
        if (! $task) {
            return false;
        }

        return $task->roleOf($user->user_id) === 'coordinator';
    }
}
