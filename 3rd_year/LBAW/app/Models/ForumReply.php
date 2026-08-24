<?php

namespace App\Models;

class ForumReply extends BaseModel
{
    protected $table = 'forum_reply';
    protected $primaryKey = 'forum_reply_id';

    protected $fillable = [
        'body',
        'created_at',
        'topic_id',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'date',
    ];

    public function topic()
    {
        return $this->belongsTo(ForumTopic::class, 'topic_id', 'forum_topic_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
