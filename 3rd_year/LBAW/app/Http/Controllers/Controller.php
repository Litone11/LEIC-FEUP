<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Routing\Controller as BaseLaravelController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller extends BaseLaravelController
{
    use AuthorizesRequests;

    /** Return authenticated user or abort */
    protected function user()
    {
        abort_unless(Auth::check(), 403);
        return Auth::user();
    }

    /** Return authenticated user's ID */
    protected function userId(): int
    {
        return $this->user()->user_id;
    }

    /** Ensure user is a member of the given project */
    protected function ensureProjectMembership(Project $project)
    {
        $user = $this->user();

        if (! $project->hasMember($user->user_id)) {
            abort(403, 'You do not belong to this project.');
        }

        if ($project->isSuspended() && ! $user->isAdmin()) {
            abort(403, 'Este projeto encontra-se suspenso.');
        }
    }

    /** Check if authenticated user is the coordinator of this project */
    //this should not be here,it's even being used
    protected function isCoordinator(Project $project): bool
    {
        return $project->roleOf($this->userId()) === 'coordinator';
    }

    /** Decode JSON safely */
    protected function jsonArray($value): array
    {
        $decoded = json_decode($value ?? '[]', true);
        return is_array($decoded) ? $decoded : [];
    }
}
