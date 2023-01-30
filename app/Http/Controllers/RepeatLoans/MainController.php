<?php

namespace App\Http\Controllers\RepeatLoans;

use App\Models\Users;
use Illuminate\Http\Request;

class MainController extends RepeatLoansController
{
    public function action(Request $request)
    {
        //Обязательные поля => текст ошибки
        $requiredParams = [
            'firstname' => 'Имя обязательно к заполнению',
            'lastname' => 'Фамилия обязательна к заполнению',
        ];

        //Проверка на обязательные поля в запросе
        foreach ($requiredParams as $key => $value) {
            if (!isset($request[$key])) {
                return response($value, 400);
            }
        }

        $firstname = $request['firstname'];         //Имя
        $lastname = $request['lastname'];           //Фамилия
        $patronymic = $request['patronymic'];       //Отчество

        $user = Users::find(self::$userId);

        $needCorrectPassport = 0;

        if($lastname != $user->lastname || $firstname != $user->firstname || $patronymic != $user->patronymic)
            $needCorrectPassport = 1;

        $userData = [
            'stage_registration' => 1,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'patronymic' => $patronymic,
            'passport_changed' => $needCorrectPassport
        ];

        Users::where('id', self::$userId)->update($userData);

        return response($needCorrectPassport, 200);
    }
}