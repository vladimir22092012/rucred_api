<?php

namespace App\Http\Controllers\StepsControllers;

use App\Models\Addresses;
use App\Models\Users;
use Illuminate\Http\Request;

class PassportController extends StepsController
{
    public function action(Request $request)
    {
        //Обязательные поля => текст ошибки
        $requiredParams = [
            'passport_serial' => 'Серия паспорта обязательна к заполнению',
            'passport_number' => 'Номер паспорта обязателен к заполнению',
            'passport_date' => 'Дата выдачи паспорта обязательна к заполнению',
            'passport_issued' => 'Поле Кем выдан обязательно к заполнению',
            'subdivision_code' => 'Код подразделения обязателен к заполнению',
            'snils' => 'СНИЛС обязателен к заполнению',
            'inn' => 'ИНН обязателен к заполнению',
            'regadressfull' => 'Адрес регистрации обязателен к заполнению',
        ];

        //Проверка на обязательные поля в запросе
        foreach ($requiredParams as $key => $value) {
            if (!isset($request[$key])) {
                return response($value, 400);
            }
        }

        $change_fio = $params['change_fio'] ?? false;      //Менялась ли ФИО
        $prev_fio = $params['prev_fio'] ?? '';           //Измененное имя
        $fio_change_date = $params['fio_change_date'] ?? '';    //Дата смены ФИО

        $passport_serial = $params['passport_serial'];
        $passport_number = $params['passport_number'];
        $passport_serial = "$passport_serial $passport_number";
        $passport_date = $params['passport_date'];
        $passport_issued = $params['passport_issued'];
        $subdivision_code = $params['subdivision_code'];

        $snils = $params['snils'];
        $inn = $params['inn'];

        $faktadressfull = $params['faktadressfull'] ?? '';      //Ажрес проживания
        $regadressfull = $params['regadressfull'];             //Адрес регистрации
        $actual_address = $params['actual_address'] ?? false;   //Совпадают ли адреса

        if ($actual_address) {
            $faktadressfull = $regadressfull;
        }

        //Проверка на дубликат
        $checkPassport = Users::checkPassport($passport_serial);
        $checkSnils = Users::checkSnils($snils);
        $checkInn = Users::checkInn($inn);

        if ($checkPassport && ($checkPassport->id != self::$userId)) {
            $msg = 'Данный паспорт уже использовался при регистрации';
            return response($msg, 406);
        }

        if ($checkSnils && ($checkSnils->id != self::$userId)) {
            $msg = 'Данный номер снилс уже использовался при регистрации';
            return response($msg, 406);
        }

        if ($checkInn && ($checkInn->id != self::$userId)) {
            $msg = 'Данный номер инн уже использовался при регистрации';
            return response($msg, 406);
        }


        $regaddress_id = Addresses::insertGetId(['adressfull' => $regadressfull]);
        $faktaddress_id = Addresses::insertGetId(['adressfull' => $faktadressfull]);

        $userData = [
            'stage_registration' => 2,
            'passport_serial' => $passport_serial,
            'passport_date' => $passport_date,
            'passport_issued' => $passport_issued,
            'subdivision_code' => $subdivision_code,
            'snils' => $snils,
            'inn' => $inn,
            'regaddress_id' => $regaddress_id,
            'faktaddress_id' => $faktaddress_id
        ];

        if ($change_fio) {
            $userData['prev_fio'] = $prev_fio;
            $userData['fio_change_date'] = $fio_change_date;
        }

        Users::where('id', self::$userId)->update($userData);

        return response('success', 200);
    }
}
