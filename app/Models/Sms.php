<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sms extends Model
{
    protected $table = 's_sms_messages';
    protected $guarded = [];
    public $timestamps = false;

    public static function getCode($phone) {

        $code = self::where('phone', $phone)
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
