<?php

namespace App\Components;

use App\Models\Users;
use App\Models\UsersTokens;
use Illuminate\Http\Request;
use App\Models\Sms as SmsDB;

class Sms
{

    protected static $login = 'RuCred';
    protected static $password = 'Ee6-eEF-w7f';


    public function send(Request $request)
    {
        $phone = $request['phone'];
        $type = $request['type'] ?? 'sms';

        if (empty($phone)) {
            return 'Не заполнен параметр phone';
        }

        $code = rand(1000, 9999);

        $message = "Ваш код подтверждения телефона на сайте Рестарт.Онлайн:  $code";

        if ($type == 'reg-doc') {
            $message = "Ваш код для подписания документов на сайте Рестарт.Онлайн:  $code";

            if (!empty($userId)) {
                $user = Users::find($userId);

                $sendTo = $user->email;
                $title = 'RuCred | Ваш код для подписания ';
                $htmlMsg = '<h1>Ваш код для подписания документов на сайте Рестарт.Онлайн: </h1>' . "<h2>$code</h2>";

                Mail::send($sendTo, $title, $htmlMsg);
            }
        }

        $phone = self::clear_phone($phone);

        $url = 'http://smsc.ru/sys/send.php?login=' . self::$login . '&psw=' . self::$password . '&phones=' . $phone . '&mes=' . $message . '';

        $resp = file_get_contents($url);

        $data = [
            'phone' => $phone,
            'user_id' => 0,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'code' => $code,
            'type' => $type,
            'response' => $resp,
            'created' => date('Y-m-d H:i:s')
        ];

        SmsDB::insert($data);
        $msg = 'Код отправлен';


        return $msg;
    }

    public function check(Request $request)
    {
        $phone = $request['phone'];
        $code = $request['code'];
        $token = $request->cookie('token');

        if (empty($phone))
            return 'Не заполнен параметр phone';

        if (empty($code))
            return 'Не заполнен параметр code';

        $checkCode = SmsDB::getCode($phone);

        if ($checkCode != $code)
            return 'Введеный код не совпадает с отправленным';

        if (!empty($token)) {
            $userId = UsersTokens::select('user_id')->where('token', $token)->first();

            if (empty($userId))
                Cookies::setToken();
            else {
                Cookies::deleteToken($userId);
                Cookies::setToken();
            }
        }
    }

    public static function clear_phone($phone)
    {
        $remove_symbols = [
            '(',
            ')',
            '-',
            ' ',
            '+'
        ];
        return str_replace($remove_symbols, '', $phone);
    }
}