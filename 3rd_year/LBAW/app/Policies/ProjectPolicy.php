<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any models.
     * //Todo :see if this makes sense
     */

    public function viewAny(User $user): bool
    {
        return true;

    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        if($user->isAdmin()) {return true;}
        return  $project->hasMember($user->user_id);
    }

    /**
     * Determine whether the user can create models.
     * //TODO: he should be logged
     */

    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool
    {
        if ($user->isAdmin()) {return true; }
        
        return $project->hasMember($user->user_id);
    }

    /**
     * Determine whether the user can delete the model.
     * //TODO
     */
    public function delete(User $user, Project $project): bool
    {
              return $user->isAdmin();

    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Project $project): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        return false;
    }
    public function coordinatorPermission(User $user, Project $project): bool
    {
        return ($user->isAdmin()|| $project->isCoordinatorFor($user->user_id));

    }

    public function updateMembers(User $user, Project $project): bool
    {
        return $this->coordinatorPermission($user, $project);
    }
}
