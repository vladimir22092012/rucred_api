<?php

namespace App\Account;

use App\Models\Addresses;
use App\Models\Branch;
use App\Models\Companies;
use App\Models\Users;

class Profile extends Account
{
    public static function get()
    {
        $userId = self::$userId;
        $user = Users::find($userId);

        $res = [
            'personal' => [
                'lastname' => $user->lastname,
                'firstname' => $user->firstname,
                'patronymic' => $user->patronymic,
                'birth' => $user->birth,
                'birth_place' => $user->birth_place
            ],
            'contact' => [
                "phone" => $user->phone_mobile,
            ],
        ];

        if (!empty($user->company_id)) {
            $company = Companies::find($user->company_id);

            $payday = 10;

            if (!empty($user->branche_id)) {
                $branche = Branch::find($user->branche_id);
                $payday = $branche->payday;
            }

            $res['work'] =
                [
                    'workplace' => $company->name,
                    'jur_address' => $company->jur_address,
                    'phys_address' => $company->phys_address,
                    'inn' => $company->inn,
                    'ogrn' => $company->ogrn,
                    'kpp' => $company->kpp,
                    'eio_fio' => $company->eio_fio,
                    'payday' => $payday
                ];
        }

        if (!empty($user->inn))
            $res['personal']['inn'] = $user->inn;
        if (!empty($user->snils))
            $res['personal']['snils'] = $user->snils;
        if (!empty($user->regaddress_id)) {
            $regaddress = Addresses::where('id', $user->regaddress_id)->first();
            $res['personal']['regaddress'] = $regaddress;
        }
        if (!empty($user->faktaddress_id)) {
            $faktaddress = Addresses::where('id', $user->faktaddress_id)->first();
            $res['personal']['regaddress'] = $faktaddress;
        }

        return response($res, 200);
    }
}