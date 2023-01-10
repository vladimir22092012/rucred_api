<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentsSchedules extends Model
{
    protected $table = 's_payments_schedules';
    protected $guarded = [];

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    public static function getSchedule($orderId) {

        $schedule = self::where('order_id', $orderId)
            ->where('actual', 1)
            ->first();

        return $schedule;
    }
}
