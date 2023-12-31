<?php

namespace App\Http\Controllers\RepeatLoans;

use App\Models\Orders;
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
            'firstname' => strtoupper($firstname),
            'lastname' => strtoupper($lastname),
            'patronymic' => strtoupper($patronymic),
            'passport_changed' => $needCorrectPassport
        ];

        Users::where('id', self::$userId)->update($userData);

        Orders::where('user_id', self::$userId)
            ->where('status', 12)
            ->where('is_archived', 0)
            ->delete();

        return response($needCorrectPassport, 200);
    }
}