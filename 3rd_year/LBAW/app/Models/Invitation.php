<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\RegularUser;
use App\Models\Project;

class Invitation extends Model
{
    protected $table='invitation';
    protected $primaryKey = 'invitation_id';
    public $timestamps = false;
    protected $fillable=['sender_id','project_id','receiver_id','is_accepted'];
    public function sender(){
        return $this->belongsTo(RegularUser::class,'sender_id','user_id');
    }
    public function receiver(){
        return $this->belongsTo(RegularUser::class,'receiver_id','user_id');
    }
    public function project(){
        return $this->belongsTo(Project::class,'project_id','project_id');
    }

}
