<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    protected $table = 's_orders';
    protected $guarded = [];
    public $timestamps = false;

    public function user()
    {
        return $this->hasOne(Users::class, 'id','user_id');
    }

    public function manager()
    {
        return $this->hasOne(Managers::class, 'id','manager_id');
    }
}
