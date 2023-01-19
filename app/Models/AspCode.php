<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AspCode extends Model
{
    protected $table = 's_asp_codes';
    protected $guarded = [];
    public $timestamps = false;

    public static function getAsp($userId, $orderId) {

        $asp = self::where('user_id', $userId)
            ->where('order_id', $orderId)
            ->latest('id')
            ->first();

        return $asp;
    }

    public static function getAspEndReg($userId, $orderId) {

        $firstAsp = Documents::where('user_id', $userId)
            ->where('order_id', $orderId)
            ->where('type', 'SOGLASIE_RABOTODATEL')
            ->first();

        $asp = self::where('user_id', $userId)
            ->where('order_id', $orderId)
            ->latest('id')
            ->first();

        if(empty($firstAsp))
            return $asp;

        if(empty($asp))
            return $firstAsp;

        if ($firstAsp->asp_id == $asp->id) {
            $asp = null;
        }

        return $asp;
    }

    public static function getFirstAsp($userId, $orderId) {

        $asp = self::where('user_id', $userId)
            ->where('order_id', $orderId)
            ->first();

        return $asp;
    }
}
