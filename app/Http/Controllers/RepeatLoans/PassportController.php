<?php

namespace App\Http\Controllers\RepeatLoans;

use App\Models\Addresses;
use App\Models\Users;
use Illuminate\Http\Request;

class PassportController extends RepeatLoansController
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
            'regadressfull' => 'Адрес регистрации обязателен к заполнению',
        ];

        //Проверка на обязательные поля в запросе
        foreach ($requiredParams as $key => $value) {
            if (!isset($request[$key])) {
                return response($value, 400);
            }
        }

        $user = Users::find(self::$userId);

        $passport_serial = $request['passport_serial'];
        $passport_number = $request['passport_number'];
        $passport_serial = "$passport_serial $passport_number";
        $passport_date = $request['passport_date'];
        $passport_issued = $request['passport_issued'];
        $subdivision_code = $request['subdivision_code'];

        $faktadressfull = $request['faktadressfull'] ?? '';      //Ажрес проживания
        $regadressfull = $request['regadressfull'];             //Адрес регистрации
        $actual_address = $request['actual_address'] ?? false;   //Совпадают ли адреса

        if ($actual_address) {
            $faktadressfull = $regadressfull;
        }

        //Проверка на дубликат
        $checkPassport = Users::checkPassport($passport_serial);

        if ($checkPassport && ($checkPassport->id != self::$userId)) {
            $msg = 'Данный паспорт уже использовался при регистрации';
            return response($msg, 406);
        }

        Addresses::where('id', $user->regaddress_id)->update(['adressfull' => $regadressfull]);
        Addresses::where('id', $user->faktaddress_id)->update(['adressfull' => $faktadressfull]);

        $userData = [
            'stage_registration' => 2,
            'passport_serial' => $passport_serial,
            'passport_date' => $passport_date,
            'passport_issued' => $passport_issued,
            'subdivision_code' => $subdivision_code,
        ];

        Users::where('id', self::$userId)->update($userData);

        return response('success', 200);
    }
}