<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsTemplates extends Model
{
    protected $table = 's_sms_templates';
    protected $guarded = [];
    public $timestamps = false;
}