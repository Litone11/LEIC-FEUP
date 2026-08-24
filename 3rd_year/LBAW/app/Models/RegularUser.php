<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\invitation;

class RegularUser extends Model
{
    protected $table = 'regular_user';
    protected $primaryKey = 'user_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'profile_pic',
        'status',
        'custom_status',
    ];

    public function setProfilePicture(?string $path): void
    {
        $this->profile_pic = $path;
        $this->save();
    }
}
