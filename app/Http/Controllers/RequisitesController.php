<?php

namespace App\Http\Controllers;

use App\Models\BankRequisite;
use App\Models\Cards;
use Illuminate\Http\Request;

class RequisitesController extends AccountController
{
    public function get()
    {
        $res = [];
        $cards = Cards::getCards(self::$userId);
        $accounts = BankRequisite::getAccounts(self::$userId);

        foreach ($cards as $key => $value) {
            $arr = [
                'id' => $value->id,
                'base_card' => $value->base_card,
                'pan' => $value->pan,
                'expdate' => $value->expdate,
                'user_id' => self::$userId,
            ];

            $res['cards'][$key] = $arr;
        }

        foreach ($accounts as $key => $value) {
            $arr = [
                'id' => $value->id,
                'pc' => $value->number,
                'akb' => $value->name,
                'bik' => $value->bik,
                'user_id' => self::$userId,
                'kc' => $value->correspondent_acc,
                'holder' => $value->holder,
                'default' => $value->default,
            ];

            $res['accounts'][$key] = $arr;
        }

        return ['status' => 200, 'resp' => $res];
    }
}
