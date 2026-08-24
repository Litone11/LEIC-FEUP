<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskList extends BaseModel
{
    protected $table = 'task_list';
    protected $primaryKey = 'task_list_id';

    protected $fillable = [
        'name',
        'description',
        'task_group_id',
        'created_by',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(TaskGroup::class, 'task_group_id', 'task_group_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'task_list_id', 'task_list_id')
            ->orderBy('due_at');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }
}
