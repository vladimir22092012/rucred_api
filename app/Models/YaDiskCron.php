<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YaDiskCron extends Model
{
    protected $table = 's_yadisk_cron';
    protected $primaryKey = 'id';
    protected $guarded = [];
    public $timestamps = false;
}