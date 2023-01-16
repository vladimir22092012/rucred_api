<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeekendCalendar extends Model
{
    protected $table = 's_weekend_calendar';
    protected $guarded = [];

    public static function checkDate($date) {
        
        $date = self::select('id', 'date')
                        ->where('date', $date)
                        ->first();

        return $date;
    }

}