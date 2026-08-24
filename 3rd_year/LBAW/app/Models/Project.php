<?php

namespace App\Models;

use App\Events\NotificationEvent;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends BaseModel
{
    protected $table = 'project';
    protected $primaryKey = 'project_id';

    protected $fillable = [
        'name',
        'description',
        'is_archived',
        'created_at',
        'color',
    ];
    protected $casts = [
        'created_at' => 'date', 
    ];

    // -------------------------------------------------------------
    // RELATIONSHIPS
    // -------------------------------------------------------------

    /** All users belonging to this project */
    public function users()
    {
        return $this->belongsToMany(User::class, 'related_to', 'project_id', 'user_id')
            ->withPivot('user_role', 'is_favorite');
    }
    public function tags()
    {
        return $this->hasMany(Tag::class, 'project_id', 'project_id');
    }


        public function notifyCoordinatorChange(): void
        {
            foreach ($this->users as $member) {

                $this->getConnection()->statement(
                    'CALL proc_tran01(?, ?)',
                    [$this->project_id, $member->user_id]
                );

                $notification = Notification::where('receiver_id', $member->user_id)
                    ->latest('notification_id')
                    ->first();

                if ($notification) {
                    event(new NotificationEvent($notification));
                }
            }
        }



    //This way it makes it easier to do policies :)
    public function getCoordinator(){
          return $this->belongsToMany(User::class, 'related_to', 'project_id', 'user_id')
            ->withPivot('user_role')->where('user_role','coordinator')->first();
    }
    /** All tasks for this project */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'project_id', 'project_id');
    }

    public function taskGroups(): HasMany
    {
        return $this->hasMany(TaskGroup::class, 'project_id', 'project_id')
            ->orderBy('name');
    }

    public function taskLists()
    {
        return $this->hasManyThrough(
            TaskList::class,
            TaskGroup::class,
            'project_id',
            'task_group_id',
            'project_id',
            'task_group_id'
        )->orderBy('name');
    }

    /** Suspension history entries */
    public function suspensionEntries()
    {
        return $this->hasMany(SuspendProject::class, 'project_id', 'project_id');
    }

    public function latestSuspension()
    {
        return $this->hasOne(SuspendProject::class, 'project_id', 'project_id')->latestOfMany('suspended_at');
    }

    // -------------------------------------------------------------
    // listing, statistics, filtering
    // -------------------------------------------------------------

    /** Add task counts and completed counts using Eloquent */
    public function scopeWithTaskStats($query)
    {
        return $query->withCount([
            'tasks as total_tasks',
            'tasks as completed_tasks' => fn($q) => $q->where('status', 'Done')
        ]);
    }

    /** Add member count */
    public function scopeWithMemberCount($query)
    {
        return $query->withCount('users as members');
    }

    /** Restrict projects to those where the user belongs */
    public function scopeForUser($query, int $userId)
    {
        return $query->whereHas('users', fn($q) =>
            $q->where('users.user_id', $userId)
        );
    }

    // -------------------------------------------------------------
    // HELPERS
    // -------------------------------------------------------------

    /** Return the user_role for this project’s member */
    public function roleOf(int $userId): ?string
    {
        return $this->users()
            ->where('users.user_id', $userId)
            ->first()
            ?->pivot
            ?->user_role;
    }

    /** Check if a user belongs to this project */
    public function hasMember(int $userId): bool
    {
        return $this->users()->where('users.user_id', $userId)->exists();
    }

    /** Sorted members (coordinators first) */
    public function sortedMembers()
    {
        return $this->users()
            ->get()
            ->map(fn($member) => tap($member, function ($m) {
                $m->role_normalized = $m->pivot->user_role === 'coordinator'
                    ? 'coordinator'
                    : 'member';
            }))
            ->sortBy(fn($m) => $m->role_normalized === 'coordinator' ? 0 : 1)
            ->values();
    }

    /** Top N members */
    public function topMembers(int $limit = 2)
    {
        return $this->sortedMembers()->take($limit);
    }

    /** Count team size */
    public function teamCount()
    {
        return $this->users()->count();
    }

    /** Summary of tasks (progress bar, ratios) */
    public function computeSummary()
    {
        $tasks = $this->tasks()
            ->orderBy('created_at')
            ->get(['status', 'created_at']);

        $total = $tasks->count();
        $done  = $tasks->where('status', 'Done')->count();

        return [
            'tasks_total'   => $total,
            'tasks_done'    => $done,
            'tasks_active'  => $tasks->where('status', '!=', 'Done')->count(),
            'progress'      => $total ? round(($done / $total) * 100) : 0,
            'tasks_ratio'   => "$done/$total",
            'start_date'    => $tasks->min('created_at')?->format('M d, Y'),
            'team_count'    => $this->teamCount(),
        ];
    }

    /**
     * Convert email
     */
    public function emailToProjectUserId(?string $email): ?int
    {
        if (!$email) return null;

        return $this->users()
        ->where('users.email', $email)
        ->value('users.user_id');
    }

    public function getProgressAttribute()
    {
        if ($this->total_tasks == 0) return 0;
        return round(($this->completed_tasks / $this->total_tasks) * 100);
    }

    public function getStatusLabelAttribute()
    {
        if ($this->isSuspended()) {
            return 'Suspended';
        }

        if ($this->is_archived || $this->progress === 100) {
            return 'Completed';
        }

        return $this->completed_tasks > 0 ? 'In Progress' : 'Planning';
    }
    public function getFormattedDateAttribute()
    {
        return $this->created_at?->format('M d, Y');
    }

    public function isCoordinatorFor(int $userId)
    {
        return $this->roleOf($userId) === 'coordinator';
    }
    public function scopeWithUserPivotFor($query, int $userId)
    {
        return $query->with(['users' => fn($q) =>
            $q->where('users.user_id', $userId)
        ]);
    }

    public function getCoordinatorId(){
        return $this->users()->wherePivot('user_role','coordinator')->first()->user_id;
    }


    public function isSuspended(): bool
    {
        $latest = $this->relationLoaded('latestSuspension')
            ? $this->latestSuspension
            : $this->latestSuspension()->first();

        return (bool) $latest;
    }

    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    public function suspensionReason(): ?string
    {
        if (! $this->isSuspended()) {
            return null;
        }

        return $this->latestSuspension?->reason;
    }
    public function removeMember(User $user): void
    {
        // Unassign tasks
        $this->tasks()
            ->where(fn ($q) =>
                $q->where('task_responsible_id', $user->user_id)
                ->orWhere('assignee_id', $user->user_id)
            )
            ->update([
                'task_responsible_id' => null,
                'assignee_id' => null,
            ]);

        // Detach user
        $this->users()->detach($user->user_id);
    }
    
    public function workload()
    {
        $this->loadMissing('users', 'tasks');

        return $this->users->map(function ($user) {
            $assigned = $this->tasks->where('assignee_id', $user->user_id);
            $responsible = $this->tasks->where('task_responsible_id', $user->user_id);

            $index = 0;

            foreach ($assigned as $t) {
                $index += match ($t->status) {
                    'Untouched' => 1.5,
                    'InProgress' => 1,
                    default => 0,
                };
            }

            foreach ($responsible as $t) {
                $index += match ($t->status) {
                    'Untouched' => 0.5,
                    'InProgress' => 0.1,
                    default => 0,
                };
            }

            return [
                'user' => $user->username,
                'assigned_count' => $assigned->count(),
                'responsible_count' => $responsible->count(),
                'total_tasks' => $assigned->count() + $responsible->count(),
                'workload_index' => $index,
            ];
        });
    }
}
