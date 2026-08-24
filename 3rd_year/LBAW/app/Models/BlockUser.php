<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockUser extends Model
{
    protected $table = 'block_user';
    protected $primaryKey = 'block_user_id';
    public $timestamps = false;

    protected $fillable = [
        'reason',
        'blocked_at',
        'unblocked_at',
        'admin_id',
        'user_id',
    ];

    public function blockedUser()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'user_id');
    }
}
