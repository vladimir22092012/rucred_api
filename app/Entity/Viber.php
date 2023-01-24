<?php

namespace App\Entity;

use App\Entity\Mail;
use App\Models\Contacts;
use App\Models\UserContactPreferred;
use App\Models\Users;
use App\Models\ViberUsers;

class Viber
{
    public static function add($userId)
    {
        $user = Users::find($userId);

        $token = md5(time());
        $token = substr($token, 1, 10);

        $subject = 'RuCred | Ссылка для привязки Viber';
        $body = '<h1>' . $_ENV['URL_CRM'] . '/redirect_api?user_id=' . $token . '</h1>';

        Mail::send($user->email, $subject, $body);

        $contact =
            [
                'user_id' => $userId,
                'token' => $token,
                'is_manager' => 0
            ];

        ViberUsers::insert($contact);

        Contacts::where(['user_id' => $userId, 'type' => 'viber'])->delete();

        $contact =
            [
                'user_id' => $userId,
                'type'    => 'viber',
                'value'   => $user->phone_mobile
            ];

        Contacts::insert($contact);

        $contact =
            [
                'user_id'            => $userId,
                'contact_type_id'    => 4
            ];

        UserContactPreferred::insert($contact);

        return 1;
    }
}