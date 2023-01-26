<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AspCode extends Model
{
    protected $table = 's_asp_codes';
    protected $guarded = [];
    public $timestamps = false;

    public static function getAsp($userId) {

        $asp = self::where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->first();

        return $asp;
    }

    public static function getAspEndReg($userId, $orderId) {

        $asp = self::where('user_id', $userId)
            ->where('order_id', $orderId)
            ->orderBy('id', 'desc')
            ->first();

        return $asp;
    }

    public static function getFirstAsp($userId, $orderId) {

        $asp = self::where('user_id', $userId)
            ->where('order_id', $orderId)
            ->first();

        return $asp;
    }
}
