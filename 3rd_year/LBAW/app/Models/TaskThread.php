<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskThread extends BaseModel
{
    protected $table = 'thread';
    protected $primaryKey = 'thread_id';

    protected $fillable = [
        'message_',
        'likes',
        'created_at',
        'task_id',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'date',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id', 'task_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class, 'thread_id', 'thread_id')
            ->orderBy('created_at');
    }
}
