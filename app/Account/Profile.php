<?php

namespace App\Account;

use App\Models\Addresses;
use App\Models\Companies;
use App\Models\Contacts;
use App\Models\Users;
use Illuminate\Http\Request;

class Profile extends Account
{
    public function get(Request $request)
    {
        $userId = self::getUserByToken($request);
        $profile = Users::getProfile($userId);
        $contacts = Contacts::getContacts($userId);
        $company = Companies::find($profile->company_id);
        $regaddress = Addresses::where('id', $profile->regaddress_id)->first();
        $faktaddress = Addresses::where('id', $profile->faktaddress_id)->first();

        $res = [
            'personal' => [
                'lastname'    => $profile->lastname,
                'firstname'   => $profile->firstname,
                'patronymic'  => $profile->patronymic,
                'inn'         => $profile->inn,
                'snils'       => $profile->snils,
                'regaddress'  => $regaddress,
                'faktaddress' => $faktaddress,
                'birth'       => $profile->birth,
                'birth_place' => $profile->birth_place
            ],
            'contact' => [
                "phone"       => $profile->phone_mobile,
            ],
            'work' => [
                'workplace'    => $company->name,
                'jur_address'  => $company->jur_address,
                'phys_address' => $company->phys_address,
                'inn'          => $company->inn,
                'ogrn'         => $company->ogrn,
                'kpp'          => $company->kpp,
                'eio_fio'      => $company->eio_fio
            ],
        ];

        foreach ($contacts as $key => $contact) {
            $res['contact'][$contact->type] = $contact->value;
        }

        return $res;
    }
}