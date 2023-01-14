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

        $change_fio = $request['change_fio'] ?? false;      //Менялась ли ФИО
        $prev_fio = $request['prev_fio'] ?? '';           //Измененное имя
        $fio_change_date = $request['fio_change_date'] ?? '';    //Дата смены ФИО

        $passport_serial = $request['passport_serial'];
        $passport_number = $request['passport_number'];
        $passport_serial = "$passport_serial $passport_number";
        $passport_date = $request['passport_date'];
        $passport_issued = $request['passport_issued'];
        $subdivision_code = $request['subdivision_code'];

        $snils = $request['snils'];
        $inn = $request['inn'];

        $faktadressfull = $request['faktadressfull'] ?? '';      //Ажрес проживания
        $regadressfull = $request['regadressfull'];             //Адрес регистрации
        $actual_address = $request['actual_address'] ?? false;   //Совпадают ли адреса

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
