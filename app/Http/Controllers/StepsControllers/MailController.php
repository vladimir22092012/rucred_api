<?php

namespace App\Http\Controllers\StepsControllers;

use App\Entity\Mail;
use App\Models\Contacts;
use App\Models\EmailMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MailController extends StepsController
{
    public function send(Request $request)
    {
        $rules =
            [
                'email' => 'required|email'
            ];

        $messages =
            [
                'required' => 'Не найден параметр email',
                'email' => 'Почта заполнена не корректно'
            ];

        $validateEmail = Validator::make($request->all(), $rules, $messages);

        if ($validateEmail->fails()) {
            return response($validateEmail->messages()->first(), 400);
        }

        $email = $request['email'];

        $isBusy = Contacts::where('value', $email)->first();

        if($isBusy && $isBusy->user_id != self::$userId)
            return response('Такая почта уже используется', 406);

        $code = random_int(1000, 9999);

        $sendTo = $email;
        $title = 'RuCred | Ваш проверочный код для подтверждения email';
        $msg = 'Ваш код для подтверждения email: ' . $code;
        $htmlMsg = '<h1>Ваш код для подтверждения email: </h1>' . "<h2>$code</h2>";

        Mail::send($sendTo, $title, $htmlMsg);

        $data = [
            'email' => $email,
            'user_id' => 0,
            'text' => $msg,
            'code' => $code,
            'text_html' => $htmlMsg,
            'created' => date('Y-m-d H:i:s')
        ];

        EmailMessage::insert($data);

        $msg = 'Код отправлен';

        return response($msg, 200);
    }

    public function check(Request $request)
    {
        $rules =
            [
                'email' => 'required|email',
                'code'  => 'required|integer'
            ];

        $messages =
            [
                'required' => 'Отсутствуют требуемые параметры',
                'email'    => 'Почта заполнена не корректно',
                'integer'  => 'Не число'
            ];

        $validateEmail = Validator::make($request->all(), $rules, $messages);

        if ($validateEmail->fails()) {
            return response($validateEmail->messages()->first(), 400);
        }

        $email  = $request['email'];
        $code   = $request['code'];

        $sendCode = EmailMessage::getCode($email);

        if(empty($sendCode))
            return response('На данную почту не отправлялось сообщение', 404);

        if ($sendCode != $code) {
            $msg = 'Введеный код не совпадает с отправленным';
            return response($msg, 406);
        }

        $result = null;
        $msg = 'Проверка прошла успешно';

        return response($msg, 200);
    }
}