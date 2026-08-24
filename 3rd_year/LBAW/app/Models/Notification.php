<?php
namespace App\Models;

use App\Models\Project;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notification';
    protected $primaryKey = 'notification_id';
    public $timestamps = false;

    protected $fillable = [
        'title',
        'message_',
        'created_at',
        'receiver_id',
        'is_read',
        'link',
    ];

    protected $casts = [
    'created_at' => 'datetime',
];
}
