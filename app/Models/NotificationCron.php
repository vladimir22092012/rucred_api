<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationCron extends Model
{
    protected $table = 's_notifications_cron';
    protected $guarded = [];
    public $timestamps = false;

}