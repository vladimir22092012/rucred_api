<?php

namespace App\Http\Controllers\RepeatLoans;

use App\Models\Users;
use Illuminate\Http\Request;

class MainController extends RepeatLoansController
{
    public function action(Request $request)
    {
        $firstname = $request['firstname'];         //Имя
        $lastname = $request['lastname'];           //Фамилия
        $patronymic = $request['patronymic'];       //Отчество

        $user = Users::find(self::$userId);

        $needCorrectPassport = 0;

        if($lastname != $user->lastname || $firstname != $user->firstname || $patronymic != $user->patronymic)
            $needCorrectPassport = 1;

        $userData = [
            'stage_registration' => 1,
        ];

        Users::where('id', self::$userId)->update($userData);

        return response($needCorrectPassport, 200);
    }
}