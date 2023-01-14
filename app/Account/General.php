<?php

namespace App\Account;

use App\Models\Addresses;
use App\Models\BankRequisite;
use App\Models\Orders;
use App\Models\OrganisationSettlement;
use App\Models\Users;
use Illuminate\Http\Request;

class General extends Account
{
    public function getStage()
    {
        $userId = self::$userId;
        $user = Users::find($userId);

        if (!empty($user)) {
            $status = 200;
            $hasStage = $user->stage_registration;
        } else {
            $status = 404;
            $hasStage = 'Такого пользователя нет';
        }

        return response($hasStage, $status);
    }

    public function getUser()
    {
        $userId = self::$userId;
        $user = Users::find($userId);

        if (!empty($user)) {
            $user->regAddress = Addresses::find($user->regaddress_id);
            $user->faktAddress = Addresses::find($user->faktaddress_id);
            $user->requisites = BankRequisite::where('user_id', $user->id)->get();
            $user->order = Orders::where('user_id', $user->id)->orderBy('id', 'desc')->first();
            $status = 200;
        } else {
            $status = 404;
            $user = 'Такого пользователя нет';
        }

        return response($user, $status);
    }

    public function getDefaultSettlement()
    {
        $settlement = OrganisationSettlement::getDefault();
        return response($settlement->id, 200);
    }
}