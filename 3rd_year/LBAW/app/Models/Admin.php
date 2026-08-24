<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $table = 'admins';
    public $timestamps = false;
    protected $primaryKey = 'user_id';
    protected $fillable= ['user_id'];
    public $incrementing = false;

}
