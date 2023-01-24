<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramUsers extends Model
{
    protected $table = 's_telegram_users';
    protected $guarded = [];
    public $timestamps = false;
}