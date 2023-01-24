<?php

namespace App\Http\Controllers\AccountControllers;

use App\Entity\Telegram;
use App\Entity\Viber;
use Illuminate\Http\Request;

class MessengersController extends AccountController
{
    public function get(Request $request)
    {

    }

    public function add(Request $request)
    {
        if($request['type'] == 'telegram')
            $resp = Telegram::add(self::$userId);
        elseif($request['type'] == 'viber')
            $resp = Viber::add(self::$userId);
        else
            return response('Такой метод не существует', 400);

        if($resp == 1)
            return response('success', 200);
        else
            return response('Ошибка привязки мессенджера', 500);
    }
}