<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskComment extends BaseModel
{
    protected $table = 'commentary';
    protected $primaryKey = 'comment_id';

    protected $fillable = [
        'message_',
        'created_at',
        'thread_id',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'date',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(TaskThread::class, 'thread_id', 'thread_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
