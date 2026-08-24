<?php

namespace App\Models;

class ForumLike extends BaseModel
{
    protected $table = 'forum_like';
    public $timestamps = false;
    protected $primaryKey = null;
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'topic_id',
        'liked_at',
    ];

    protected $casts = [
        'liked_at' => 'date',
    ];

    public function topic()
    {
        return $this->belongsTo(ForumTopic::class, 'topic_id', 'forum_topic_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
