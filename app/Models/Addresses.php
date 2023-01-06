<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Addresses extends Model
{
    protected $table = 's_addresses';
    protected $guarded = [];
    public $timestamps = false;
}
