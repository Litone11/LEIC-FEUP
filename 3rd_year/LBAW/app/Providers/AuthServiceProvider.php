<?php

namespace App\Providers;

use App\Models\Admin;
use App\Models\Invitation;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\TaskGroup;
use App\Models\TaskList;
use App\Models\User;
use App\Policies\AdminPolicy;
use App\Policies\InvitationPolicy;
use App\Policies\NotificationPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\TagPolicy;
use App\Policies\TaskCommentPolicy;
use App\Policies\TaskGroupPolicy;
use App\Policies\TaskListPolicy;
use App\Policies\TaskPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     */
    protected $policies = [
        Task::class => TaskPolicy::class,
        User::class => UserPolicy::class,
        Project::class => ProjectPolicy::class,
        Admin::class => AdminPolicy::class,
        Tag::class => TagPolicy::class,
        Notification::class => NotificationPolicy::class,
        Invitation::class => InvitationPolicy::class,
        TaskComment::class => TaskCommentPolicy::class,
        TaskGroup::class => TaskGroupPolicy::class,
        TaskList::class => TaskListPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        Gate::define('admin-access', function (User $user) {
            return $user->isAdmin();
        });
    }
}
