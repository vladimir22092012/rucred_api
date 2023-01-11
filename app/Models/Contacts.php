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

    public static function checkEmail($email)
    {
        $contact = self::select('user_id')
            ->where('type', 'email')
            ->where('value', $email)
            ->first();

        return $contact;
    }

    public static function checkViber($viber)
    {
        $contact = self::select('user_id')
            ->where('type', 'viber')
            ->where('value', $viber)
            ->first();

        return $contact;
    }

    public static function checkWhatsapp($whatsapp)
    {
        $contact = self::select('user_id')
            ->where('type', 'whatsapp')
            ->where('value', $whatsapp)
            ->first();

        return $contact;
    }

    public static function checkTelegram($telegram)
    {
        $contact = self::select('user_id')
            ->where('type', 'telegram')
            ->where('value', $telegram)
            ->first();

        return $contact;
    }
}
