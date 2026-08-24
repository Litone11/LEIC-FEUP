<?php
namespace App\Models;

use App\Models\Project;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $table = 'tag';
    protected $primaryKey = 'tag_id';
    public $timestamps = false;

    protected $fillable = [
        'name','project_id'
    ];

    public function tasks(){
    return $this->belongsToMany(Task::class, 'has_tags', 'tag_id', 'task_id');
}


    public function project(){
    return $this->belongsTo(Project::class, 'project_id', 'project_id');
}

}
