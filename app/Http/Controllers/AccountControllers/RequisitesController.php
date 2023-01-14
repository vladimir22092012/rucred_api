<?php

namespace App\Http\Controllers\AccountControllers;

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

        return response($res, 200);
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
                return response($value, 400);
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

        return response('Карта успешно добавлена', 200);
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
                return response($value, 400);
            }
        }

        $alreadyExist = BankRequisite::where('number', $request['number'])->first();

        if(!empty($alreadyExist))
            return response('Такой счет уже существует', 406);

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

        return response('Счет успешно добавлен', 200);
    }

    public function changeRequisites(Request $request)
    {
        $requisite_id = $request['requisite_id'];

        if(empty($requisite_id))
            return ['status' => 404, 'resp' => 'requisite_id is empty'];

        BankRequisite::setZeroDefault(self::$userId);

        BankRequisite::where('id', $requisite_id)->update(['default' => 1]);

        return ['status' => 200, 'resp' => 'Данные сохранены'];
    }
}
