<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cards extends Model
{
    protected $table = 's_cards';
    protected $guarded = [];
    public $timestamps = false;

    public static function getCards($userId)
    {
        $cards = self::select('id', 'pan', 'expdate', 'base_card')
            ->where('user_id', $userId)
            ->get();

        return $cards;
    }

    public static function getDefault($userId)
    {
        $card = self::where('user_id', $userId)
            ->where('base_card', 1)
            ->first();
        if (is_null($card)) {
            $card = self::where('user_id', $userId)
                ->first();
        }

        return $card;
    }

    //"обнуление" всех карт по умолчанию
    public static function setZeroDefault($userId)
    {
        self::where('user_id', $userId)
            ->where('base_card', 1)
            ->update(['base_card' => 0]);
    }

    public static function checkPan($pan)
    {
        $card = self::select('user_id')
            ->where('pan', $pan)
            ->first();

        return $card;
    }
}
