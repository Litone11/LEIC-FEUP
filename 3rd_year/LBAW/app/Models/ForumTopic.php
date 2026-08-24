<?php

namespace App\Models;

class ForumTopic extends BaseModel
{
    protected $table = 'forum_topic';
    protected $primaryKey = 'forum_topic_id';

    protected $fillable = [
        'title',
        'body',
        'created_at',
        'project_id',
        'user_id',
        'task_id',
    ];

    protected $casts = [
        'created_at' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id', 'task_id');
    }

    public function replies()
    {
        return $this->hasMany(ForumReply::class, 'topic_id', 'forum_topic_id')
            ->orderBy('created_at')
            ->orderBy('forum_reply_id');
    }

    public function likes()
    {
        return $this->hasMany(ForumLike::class, 'topic_id', 'forum_topic_id');
    }

    public function likedByUser(int $userId): bool
    {
        if ($this->relationLoaded('likes')) {
            return $this->likes->contains('user_id', $userId);
        }

        return $this->likes()->where('user_id', $userId)->exists();
    }
}
