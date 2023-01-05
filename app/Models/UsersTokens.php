<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersTokens extends Model
{
    protected $table = 's_users_tokens';
    protected $guarded = [];
    public $timestamps = false;
}
