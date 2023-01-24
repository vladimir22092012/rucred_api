<?php

namespace App\Application\Actions\Messengers;

use App\Entity\Sms;
use App\Models\Contacts;
use App\Models\SmsTemplates;
use App\Models\TelegramUsers;
use App\Models\UserContactPreferred;
use App\Models\Users;

class Telegram
{
    public static function add($userId)
    {

        $user = Users::find($userId);

        $token = md5(time());
        $token = substr($token, 1, 10);

        $template = SmsTemplates::where('id', 5)->first();
        $message = str_replace('$user_token', $token, $template->template);

        Sms::sendTelegramLink($user->phone_mobile, $message);

        $contact =
            [
                'user_id' => $userId,
                'token' => $token,
                'is_manager' => 0
            ];

        TelegramUsers::insert($contact);

        Contacts::where(['user_id' => $userId, 'type' => 'telegram'])->delete();

        $contact =
            [
                'user_id' => $userId,
                'type'    => 'telegram',
                'value'   => $user->phone_mobile
            ];

        Contacts::insert($contact);

        $contact =
            [
                'user_id'            => $userId,
                'contact_type_id'    => 3
            ];

        UserContactPreferred::insert($contact);

        return 1;
    }
}