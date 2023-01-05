<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    protected $table = 's_users';
    protected $guarded = [];
    public $timestamps = false;

    public function orders()
    {
        return $this->hasMany(Orders::class, 'user_id','id');
    }
}
