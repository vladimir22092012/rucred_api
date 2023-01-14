<?php

namespace App\Http\Controllers\StepsControllers;

use App\Entity\Mail;
use App\Models\EmailMessage;
use Illuminate\Http\Request;

class MailController extends StepsController
{
    public function send(Request $request)
    {
        $email = $request['email'];

        $code = random_int(1000, 9999);

        $sendTo   = $email;
        $title    = 'RuCred | Ваш проверочный код для подтверждения email';
        $msg      = 'Ваш код для подтверждения email: ' . $code;
        $htmlMsg  = '<h1>Ваш код для подтверждения email: </h1>' . "<h2>$code</h2>";

        Mail::send($sendTo, $title, $htmlMsg);

        $data = [
            'email'     => $email,
            'user_id'   => 0,
            'text'      => $msg,
            'code'      => $code,
            'text_html' => $htmlMsg,
            'created'   => date('Y-m-d H:i:s')
        ];

        EmailMessage::insert($data);

        $msg = 'Код отправлен';

        return response($msg, 200);
    }
}