<?php

namespace App\Models;

use App\Events\NotificationEvent;
use App\Models\Project;
use App\Models\User;
use App\Models\Tag;
class Task extends BaseModel
{
    protected $table = 'task';
    protected $primaryKey = 'task_id';

    protected $fillable = [
        'name',
        'description',
        'status',
        'priority',
        'effort',
        'nr_comment',
        'created_at',
        'completed_at',
        'due_at',
        'project_id',
        'task_responsible_id',
        'assignee_id',
    ];

    protected $casts = [
        'created_at'   => 'date',
        'completed_at' => 'date',
        'due_at'       => 'date',
        'nr_comment'   => 'integer',
    ];

    // -------------------------------------------------------------
    // RELATIONSHIPS
    // -------------------------------------------------------------

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function discussionThread()
    {
        return $this->hasOne(TaskThread::class, 'task_id', 'task_id');
    }

    public function taskComments()
    {
        return $this->hasManyThrough(
            TaskComment::class,
            TaskThread::class,
            'task_id',
            'thread_id',
            'task_id',
            'thread_id'
        )->orderBy('commentary.created_at');
    }

    public function predecessorLinks()
    {
        return $this->hasMany(TaskDependency::class, 'successor_task_id', 'task_id')
            ->with('predecessor');
    }

    public function successorLinks()
    {
        return $this->hasMany(TaskDependency::class, 'predecessor_task_id', 'task_id')
            ->with('successor');
    }

    public function responsible()
    {
        return $this->belongsTo(User::class, 'task_responsible_id', 'user_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id', 'user_id');
    }

    public function tags()
    {
        return $this->belongsToMany(
            Tag::class,
            'has_tags',
            'task_id', 
            'tag_id',
        )->withPivot('task_id', 'tag_id');
    }

    public function taskList()
    {
        return $this->belongsTo(TaskList::class, 'task_list_id', 'task_list_id');
    }

    public function tagIds()
    {
        return $this->tags()->pluck('tag.tag_id');
    }
    // -------------------------------------------------------------
    // AUTHORIZATION HELPERS
    // -------------------------------------------------------------

    public function ensureDiscussionThread(int $userId): TaskThread
    {
        $thread = $this->discussionThread()->first();

        if ($thread) {
            return $thread;
        }

        return TaskThread::create([
            'message_'  => 'Discussão da tarefa ' . $this->name,
            'likes'     => 0,
            'created_at'=> now()->toDateString(),
            'task_id'   => $this->task_id,
            'user_id'   => $userId,
        ]);
    }

    public function roleOf($userId)
    {
        $project = $this->getRelationValue('project') ?? $this->project()->first();
        return $project?->roleOf($userId);
    }

/*     public function canBeEditedBy($user): bool
    {
        $role = $this->roleOf($user->user_id);

        return $role === 'coordinator'
            || $this->task_responsible_id == $user->user_id;
    } */

/*     public function canBeDeletedBy($user)
    {
        $role = $this->roleOf($user->user_id);

        return $role === 'coordinator'
            || $this->task_responsible_id == $user->user_id;
    } */
    // -------------------------------------------------------------
    // SCOPES
    // -------------------------------------------------------------

    public function scopeVisibleTo($query, int $userId, int $projectId)
    {
        return $query
            ->where('project_id', $projectId)
            ->whereHas('project.users', fn($q) =>
                $q->where('users.user_id', $userId)
            )
            ->with(['responsible', 'assignee', 'project']);
    }

    public function scopeSearch($query, ?string $needle)
    {
        if (!$needle) return $query;

        return $query->where(function ($q) use ($needle) {
            $q->where('name', 'ILIKE', "%$needle%")
              ->orWhere('description', 'ILIKE', "%$needle%");
        });
    }

    public function scopeOrderByField($query, string $field, string $direction = 'asc')
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return match ($field) {

            'name',
            'status',
            'priority',
            'created_at',
            'due_at' =>
                $query->orderBy($field, $direction),

            'responsible' =>
                $query->whereHas('responsible')
                      ->with('responsible')
                      ->orderBy(
                          User::select('username')
                              ->whereColumn('users.user_id', 'task.task_responsible_id'),
                          $direction
                      ),

            'assignee' =>
                $query->whereHas('assignee')
                      ->with('assignee')
                      ->orderBy(
                          User::select('username')
                              ->whereColumn('users.user_id', 'task.assignee_id'),
                          $direction
                      ),

            default =>
                $query->orderBy('due_at', $direction),
        };
    }
   
    // -------------------------------------------------------------
    // DATA FOR UI
    // -------------------------------------------------------------

    public function toArrayForProject($user): array
    {
        $role = $this->roleOf($user->user_id);

        return [
            'id'          => $this->task_id,
            'name'        => $this->name,
            'description' => $this->description,

            'status'      => $this->status,
            'priority'    => $this->priority,

            'created_at'   => $this->prettyDate($this->created_at),
            'due_at'       => $this->prettyDate($this->due_at),
            'raw_due_at'   => $this->htmlDate($this->due_at),
            'completed_at' => $this->prettyDate($this->completed_at),

            'project' => $this->project,

            'responsible_name' => $this->responsible?->username,
            'assignee_name'    => $this->assignee?->username,
            'task_list_id'     => $this->task_list_id,
            'task_list_name'   => $this->taskList?->name,

            'task_responsible_id' => $this->task_responsible_id,
            'assignee_id'         => $this->assignee_id,

            'is_coordinator' => $role === 'coordinator',
            'is_responsible' => $this->task_responsible_id == $user->user_id,

            'can_view' => (
                $role === 'coordinator' ||
                $this->task_responsible_id == $user->user_id ||
                $this->assignee_id == $user->user_id
            ),

            'can_edit' => (
                $role === 'coordinator' ||
                $this->task_responsible_id == $user->user_id
            ),
            'tags'=> $this->tags->map(fn($t) => ['tag_id' => $t->tag_id, 'name' => $t->name])->toArray(),

        ];
    }
    public function notifyAssignment(int $assigneeId): void
    {
        $lastNotificationId = Notification::max('notification_id') ?? 0;

        $this->getConnection()->statement(
            'CALL proc_tran05(?, ?)',
            [$this->task_id, $assigneeId]
        );

        $notifications = Notification::join(
                'assigned_notification',
                'notification.notification_id',
                '=',
                'assigned_notification.notification_id'
            )
            ->where('assigned_notification.task_id', $this->task_id)
            ->where('notification.notification_id', '>', $lastNotificationId)
            ->orderBy('notification.notification_id')
            ->select('notification.*')
            ->get();

        foreach ($notifications as $notification) {
            event(new NotificationEvent($notification));
        }
    }



  public function notifyMarkAsDone(): void
{
    $lastNotificationId = Notification::max('notification_id') ?? 0;

    // DB creates notifications
    $this->getConnection()->statement(
        'CALL proc_tran04(?)',
        [$this->task_id]
    );

    $notifications = Notification::join(
            'complete_notification',
            'notification.notification_id',
            '=',
            'complete_notification.notification_id'
        )
        ->where('complete_notification.task_id', $this->task_id)
        ->where('notification.notification_id', '>', $lastNotificationId)
        ->orderBy('notification.notification_id')
        ->select('notification.*')
        ->get();

    foreach ($notifications as $notification) {
        event(new NotificationEvent($notification));
    }
}

}
