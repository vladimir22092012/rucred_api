<?php

namespace App\Http\Controllers\StepsControllers;

use App\Models\Contacts;
use App\Models\Documents;
use App\Models\UserContactPreferred;
use App\Models\Users;
use Illuminate\Http\Request;

class ContactController extends StepsController
{
    public function action(Request $request)
    {
        $userId = self::$userId;

        $email    = $request['email'] ?? null;
        $viber    = $request['viber'] ?? null;
        $telegram = $request['telegram'] ?? null;
        $facebook = $request['facebook'] ?? null;
        $ok       = $request['ok'] ?? null;
        $vk       = $request['vk'] ?? null;

        if (empty($email))
            return response('Поле почты не должно быть пустым', 400);

        $foreign_flag      = $request['fio_foreign_flagrelative'] ?? 0; //иностранное публичное должностное лицо
        $foreign_husb_wife = $request['foreign_husb_wife'] ?? 0;        //иностранный муж\жена да\нет
        $fio_public_spouse = $request['fio_public_spouse'] ?? '';       //ФИО Супруги(-а)
        $foreign_relative  = $request['foreign_relative'] ?? 0;         //Родственник иностранного публичного должностного лица
        $fio_relative      = $request['fio_relative'] ?? '';            //ФИО родственника ИПДЛ

        $contact_preferred = $request['contact_preferred_id'];          //Предпочтительные способы связи

        //В бд сохраняется как 1 - нет, 2 - да (из-за js в црм). В апи приходит 0 - нет, 1 - да
        $foreign_flag++;
        $foreign_husb_wife++;
        $foreign_relative++;

        $userData = [
            'foreign_flag'       => $foreign_flag,
            'foreign_husb_wife'  => $foreign_husb_wife,
            'fio_public_spouse'  => $fio_public_spouse,
            'foreign_relative'   => $foreign_relative,
            'fio_relative'       => $fio_relative,
            'email'              => $email,
            'stage_registration' => 3,
        ];

        //Проверка на дубликат
        $checkEmail = Contacts::checkEmail($email);

        if ($checkEmail && ($checkEmail->user_id != $userId)) {
            $msg = 'Данный email уже использовался при регистрации';
            return response($msg, 406);
        }

        if ($viber) {
            $checkViber = Contacts::checkViber($viber);

            if ($checkViber && ($checkViber->user_id != $userId)) {
                $msg = 'Данный viber уже использовался при регистрации';
                return response($msg, 406);
            }
        }

        if ($telegram) {
            $checkTelegram = Contacts::checkTelegram($telegram);

            if ($checkTelegram && ($checkTelegram->user_id != $userId)) {
                $msg = 'Данный telegram уже использовался при регистрации';
                return response($msg, 406);
            }
        }

        Users::where('id', $userId)->update($userData);

        $contactData = [
            'email'     => $email,
            'viber'     => $viber,
            'telegram'  => $telegram,
            'facebook'  => $facebook,
            'ok'        => $ok,
            'vk'        => $vk
        ];

        foreach ($contactData as $key => $value) {
            if (is_null($value) || strlen($value) < 5) {
                continue;
            }
            Contacts::updateOrCreate(
                ['user_id' => $userId, 'type' => $key],
                ['value' => $value]
            );
        }

        foreach ($contact_preferred as $key => $value) {
            UserContactPreferred::firstOrCreate(
                ['user_id' => $userId, 'contact_type_id' => $value]
            );
        }

        //Создаем документы для следующих шагов регистрации
        Documents::createDocsForRegistration($userId);

        return response('success', 200);
    }
}