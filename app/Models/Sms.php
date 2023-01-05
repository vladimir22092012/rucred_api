<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sms extends Model
{
    protected $table = 's_sms_messages';
    protected $guarded = [];
    public $timestamps = false;
}
