<?php

namespace App\Components;

use App\Models\Users;
use \Illuminate\Support\Facades\Request;

class Sms
{
    public function send(Request $request)
    {
        $phone = $request['phone'];
        $type  = $request['type'] ?? 'sms';

        if (empty($phone)) {
            return 'Не заполнен параметр phone';
        }

        $code = rand(1000, 9999);

        $message = "Ваш код подтверждения телефона на сайте Рестарт.Онлайн:  $code";

        if ($type == 'reg-doc') {
            $message = "Ваш код для подписания документов на сайте Рестарт.Онлайн:  $code";

            if(!empty($userId))
            {
                $user = Users::find($userId);

                $sendTo   = $user->email;
                $title    = 'RuCred | Ваш код для подписания ';
                $htmlMsg  = '<h1>Ваш код для подписания документов на сайте Рестарт.Онлайн: </h1>' . "<h2>$code</h2>";

                Mail::send($sendTo, $title, $htmlMsg);
            }
        }

        $resp = Sms::send($phone, $message);

        $data = [
            'phone'    => $phone,
            'user_id'  => 0,
            'ip'       => $_SERVER['REMOTE_ADDR'],
            'code'     => $code,
            'type'     => $type,
            'response' => $resp,
            'created'  => date('Y-m-d H:i:s')
        ];

        Sms::insert($data);
        $msg = 'Код отправлен';


        return $msg;
    }
}