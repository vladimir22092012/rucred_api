<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Managers extends Model
{
    protected $table = 's_managers';
    protected $guarded = [];
    public $timestamps = false;

    public function orders()
    {
        return $this->hasMany(Orders::class, 'manager_id','id');
    }
}
