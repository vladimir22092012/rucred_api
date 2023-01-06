<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contacts extends Model
{
    protected $table = 's_contacts';
    protected $guarded = [];
    public $timestamps = false;

    public static function getContacts($userId)
    {
        $contacts = self::where('user_id', $userId)->get();
        return $contacts;
    }
}
