<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operations extends Model
{
    protected $table = 's_operations';
    protected $guarded = [];
    public $timestamps = false;


    public static function getOperations($orderId)
    {

        $operations = self::where('order_id', $orderId)
            ->get();

        return $operations;
    }

    public function order()
    {
        return $this->belongsTo(Orders::class);
    }
}
