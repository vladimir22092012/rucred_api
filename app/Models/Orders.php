<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    protected $table = 's_orders';
    protected $guarded = [];
    public $timestamps = false;

    static public $orderStatuses = [
        0 => 'Новая',
        1 => 'Принята',
        2 => 'А.Подтверждена',
        4 => 'Подписан',
        5 => 'Выдан',
        6 => 'Не удалось выдать',
        7 => 'Погашен',
        8 => 'Отказ клиента',
        9 => 'Выплачиваем',
        10 => 'Отправлено',
        11 => 'М.Отказ',
        12 => 'Черновик',
        13 => 'Р.Нецелесообразно',
        14 => 'Р.Подтверждена',
        15 => 'Р.Отклонена',
        16 => 'Удалена',
        17 => 'Рестр.Запрошена',
        18 => 'Рестр.Подготовлена',
        19 => 'Реструктуризирован',
        20 => 'А.Отказ'
    ];

    public static function getOrders($userId) {

        $orders = self::where('user_id', $userId)
            ->get();

        foreach ($orders as $key => $order) {
            $orders[$key]->status = self::$orderStatuses[$order->status];
        }

        return $orders;
    }

    public static function getActiveOrders($userId)
    {
        $orders = self::where('user_id', $userId)
            ->where('status', 5)
            ->get();

        foreach ($orders as $key => $order) {
            $orders[$key]->status = self::$orderStatuses[$order->status];
        }

        return $orders;
    }

    public static function getUnfinished($userId)
    {
        $order = self::where('user_id', $userId)
            ->where('status', 12)
            ->where('is_archived', 0)
            ->orderBy('id', 'desc')
            ->first();

        return $order;
    }

    public function user()
    {
        return $this->hasOne(Users::class, 'id','user_id');
    }

    public function manager()
    {
        return $this->hasOne(Managers::class, 'id','manager_id');
    }
}
