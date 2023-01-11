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

    public function addCard(Request $request)
    {
        //Обязательные поля => текст ошибки
        $requiredParams = [
            'pan' => 'Номер карты обязателен к заполнению',
            'expdate' => 'Дата окончания карты обязательна к заполнению',
        ];

        //Проверка на обязательные поля в запросе
        foreach ($requiredParams as $key => $value) {
            if (!isset($request[$key])) {
                return ['status' => 500, 'resp' => $value];
            }
        }

        $pan = $request['pan'];
        $expdate = $request['expdate'];
        $default = $request['default'] ?? 0;

        //Cбрасываем предудущий выбор карты по умолчанию
        if ($default == 1) {
            Cards::setZeroDefault(self::$userId);
        }

        $userData = [
            'user_id' => self::$userId,
            'pan' => $pan,
            'expdate' => $expdate,
            'base_card' => $default
        ];

        Cards::insert($userData);

        return ['status' => 200, 'resp' => 'Карта успешно добавлена'];
    }

    public function addAccount(Request $request)
    {
        //Обязательные поля => текст ошибки
        $requiredParams = [
            'number'             => 'Номер счета обязателен к заполнению',
            'name'               => 'Название банка(АКБ) обязателено к заполнению',
            'bik'                => 'БИК обязателен к заполнению',
            'holder'             => 'ФИО владельца обязательно к заполнению',
            'correspondent_acc'  => 'К/С обязателен к заполнению',
        ];

        //Проверка на обязательные поля в запросе
        foreach ($requiredParams as $key => $value) {
            if (!isset($request[$key])) {
                return ['status' => 500, 'resp' => $value];
            }
        }

        $number            = $request['number'];
        $name              = $request['name'];
        $bik               = $request['bik'];
        $holder            = $request['holder'];
        $correspondent_acc = $request['correspondent_acc'];

        //Cбрасываем предудущий выбор счета по умолчанию
        BankRequisite::setZeroDefault(self::$userId);

        $userData = [
            'user_id'           => self::$userId,
            'number'            => $number,
            'name'              => $name,
            'bik'               => $bik,
            'holder'            => $holder,
            'correspondent_acc' => $correspondent_acc,
            'default'           => 1
        ];

        BankRequisite::insert($userData);

        return ['status' => 200, 'resp' => 'Счет успешно добавлен'];
    }
}