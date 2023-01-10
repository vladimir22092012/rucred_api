<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contracts extends Model
{
    protected $table = 's_contracts';
    protected $guarded = [];
    public $timestamps = false;

    public static function getContractByOrder($orderId) {

        $contract = self::where('order_id', $orderId)
            ->first();


        return $contract;
    }

    public static function countContractByOrder($orderId) {

        $contract = self::where('order_id', $orderId)
            ->count();


        return $contract;
    }
}
