<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 's_groups';
    protected $guarded = [];

    public function companies()
    {
      return $this->hasMany(Companies::class);
    }

}