<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailMessage extends Model
{
    protected $table = 's_email_messages';
    protected $guarded = [];
    public $timestamps = false;

    public static function getCode($email) {
        //todo: проверку по времени
        $code = self::where('email', $email)
            ->latest('id')
            ->first();

        if (is_null($code)) {
            $code = false;
        } else {
            $code = $code->code;
        }

        return $code;
    }
}
