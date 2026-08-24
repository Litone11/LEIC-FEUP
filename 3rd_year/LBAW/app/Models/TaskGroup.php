<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskGroup extends BaseModel
{
    protected $table = 'task_group';
    protected $primaryKey = 'task_group_id';

    protected $fillable = [
        'name',
        'description',
        'label',
        'project_id',
        'created_by',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function lists(): HasMany
    {
        return $this->hasMany(TaskList::class, 'task_group_id', 'task_group_id')
            ->orderBy('name');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }
}
