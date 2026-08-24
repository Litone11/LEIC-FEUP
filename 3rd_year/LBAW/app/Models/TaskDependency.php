<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskDependency extends BaseModel
{
    protected $table = 'task_dependency';
    protected $primaryKey = 'task_dependency_id';

    protected $fillable = [
        'predecessor_task_id',
        'successor_task_id',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function predecessor(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'predecessor_task_id', 'task_id');
    }

    public function successor(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'successor_task_id', 'task_id');
    }
}
