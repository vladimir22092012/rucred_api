<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Companies extends Model
{
    protected $table = 's_companies';
    protected $guarded = [];
    public $timestamps = false;

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class, 'company_id', 'id');
    }
}
