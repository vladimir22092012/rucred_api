<?php

namespace App\Entity;

use App\Models\Users;
use App\Models\UsersTokens;
use Illuminate\Http\Request;
use App\Models\SmsMessages as SmsDB;
use Illuminate\Support\Facades\Cookie;

class Sms
{

    protected static $login = 'RuCred';
    protected static $password = 'Ee6-eEF-w7f';


    public function send(Request $request)
    {
        $phone = $request['phone'];
        $step = $request['step'];

        if (empty($phone))
            return response(['status' => 400, 'resp' => 'Не заполнен параметр phone'], 400);

        $phone = self::clear_phone($phone);

        $userId = Users::where('phone_mobile', $phone)->first();

        if (empty($userId) && $step == 'auth')
            return response(['status' => 404, 'resp' => 'Такого клиента нет'], 404);


        $code = rand(1000, 9999);

        $message = "Ваш код подтверждения телефона на сайте Рестарт.Онлайн:  $code";

        if ($step == 'reg-doc') {
            $message = "Ваш код для подписания документов на сайте Рестарт.Онлайн:  $code";

            if (!empty($userId)) {
                $user = Users::find($userId);

                $sendTo = $user->email;
                $title = 'RuCred | Ваш код для подписания ';
                $htmlMsg = '<h1>Ваш код для подписания документов на сайте Рестарт.Онлайн: </h1>' . "<h2>$code</h2>";

                Mail::send($sendTo, $title, $htmlMsg);
            }
        }

        $url = 'http://smsc.ru/sys/send.php?login=' . self::$login . '&psw=' . self::$password . '&phones=' . $phone . '&mes=' . $message . '';

        $resp = file_get_contents($url);

        $data = [
            'phone' => $phone,
            'user_id' => 0,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'code' => $code,
            'type' => 'sms',
            'response' => $resp,
            'created' => date('Y-m-d H:i:s')
        ];

        SmsDB::insert($data);
        $msg = 'Код отправлен';


        return ['status' => 200, 'resp' => $msg];
    }

    public function check(Request $request)
    {
        $phone = $request['phone'];
        $code = $request['code'];
        $step = $request['step'];

        if (empty($phone))
            return 'Не заполнен параметр phone';

        if (empty($code))
            return 'Не заполнен параметр code';

        $checkCode = SmsDB::getCode($phone);

        if ($checkCode != $code)
            return 'Введеный код не совпадает с отправленным';

        $user = Users::where('phone_mobile', $phone)->first();


        if (!empty($user) && $step == 'auth') {
            Cookies::doExpireTokens($user->id);
            $userId = $user->id;
        } else {
            $userId = 0;
        }

        $rand = rand(1, 999999);
        $newToken = md5((string)$rand);

        $insert =
            [
                'token' => $newToken,
                'user_id' => $userId
            ];

        UsersTokens::insert($insert);

        if (!empty($user) && $user->stage_registration == 8 && $step == 'reg')
            return ['status' => 201, 'resp' => $newToken];
        elseif (!empty($user) && $user->stage_registration != 8 && $step == 'reg')
            return ['status' => 202, 'resp' => ['stage' => $user->stage_registration, 'token' => $newToken]];
        else
            return ['status' => 200, 'resp' => $newToken];
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