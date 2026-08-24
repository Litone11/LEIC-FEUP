<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuspendProject extends Model
{
    protected $table = 'suspend_project';
    protected $primaryKey = 'suspend_project_id';
    public $timestamps = false;

    protected $fillable = [
        'reason',
        'suspended_at',
        'unsuspended_at',
        'admin_id',
        'project_id',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'user_id');
    }
}
