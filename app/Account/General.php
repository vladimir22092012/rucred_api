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

        if (!empty($user))
            $hasStage = $user->stage_registration;
        else
            $hasStage = 'Такого пользователя нет';

        return ['status' => 200, 'resp' => $hasStage];
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

        } else
            $user = 'Такого пользователя нет';

        return ['status' => 200, 'resp' => $user];
    }

    public function getDefaultSettlement()
    {
        $settlement = OrganisationSettlement::getDefault();

        return ['status' => 200, 'resp' => $settlement->id];
    }
}