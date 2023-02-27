<?php

namespace App\Http\Controllers\StepsControllers;

use App\Models\BankRequisite;
use App\Models\Cards;
use App\Models\Orders;
use App\Models\OrganisationSettlement;
use App\Models\Setting;
use App\Models\Users;
use App\Models\WeekendCalendar;
use Illuminate\Http\Request;

class RequisitesController extends StepsController
{
    public function addAccount(Request $request)
    {
        $userId = self::$userId;

        //Обязательные поля => текст ошибки
        $requiredParams = [
            'number' => 'Номер счета обязателен к заполнению',
            'name' => 'Название банка(АКБ) обязателено к заполнению',
            'bik' => 'БИК обязателен к заполнению',
            'holder' => 'ФИО владельца обязательно к заполнению',
            'correspondent_acc' => 'К/С обязателен к заполнению',
            'inn_holder' => 'ИНН держателя счета обязателен к заполнению'
        ];

        //Проверка на обязательные поля в запросе
        foreach ($requiredParams as $key => $value) {
            if (!isset($request[$key])) {
                return response($value, 400);
            }
        }

        $number = $request['number'];
        $name = $request['name'];
        $bik = $request['bik'];
        $holder = strtoupper($request['holder']);
        $correspondent_acc = $request['correspondent_acc'];
        $orderId = $request['orderId'] ?? '';
        $innHolder = $request['inn_holder'];

        //Проверка на дубликат
        $checkNumber = BankRequisite::checkNumber($number);

        //Проверка чтобы не совпадал ИНН клиента и Держателя счета
        $user = Users::find($userId);
        $userFio = $user->lastname.' '.$user->firstname.' '.$user->patronymic;

        if ($user->inn == $innHolder && $userFio != $holder)
            return response('При получении займа на счет третьего лица, ваш ИНН не должен совпадать с ИНН держателя счета', 406);

        if ($checkNumber && ($checkNumber->user_id != $userId))
            return response('Такой счет уже существует', 406);

        //Cбрасываем предудущий выбор счета по умолчанию

        BankRequisite::setZeroDefault($userId);

        $bankData = [
            'user_id' => $userId,
            'number' => $number,
            'name' => $name,
            'bik' => $bik,
            'holder' => $holder,
            'correspondent_acc' => $correspondent_acc,
            'inn_holder' => $innHolder,
            'default' => 1
        ];

        BankRequisite::insert($bankData);

        Users::where('id', $userId)->update(['stage_registration' => 5]);

        $default_settlement = OrganisationSettlement::getDefault();
        $timeOfTransitionToNextBankingDay = date(
            'H:i',
            strtotime(Setting::whereName('time_of_transition_to_the_next_banking_day')->first()->value)
        );

        $start_date = date('Y-m-d');

        if ($default_settlement->id == 3 && date('H:i') >= $timeOfTransitionToNextBankingDay) {
            $start_date = date('Y-m-d', strtotime('+1 days'));
        }

        if ($default_settlement->id == 2) {
            if (date('H:i') >= $timeOfTransitionToNextBankingDay) {
                $start_date = date('Y-m-d', strtotime('+2 days'));
            } else {
                $start_date = date('Y-m-d', strtotime('+1 days'));
            }
        }

        $check_date = WeekendCalendar::checkDate($start_date);

        if (!empty($check_date)) {
            for ($i = 0; $i <= 15; $i++) {
                $check_date = WeekendCalendar::checkDate($start_date);

                if (empty($check_date)) {
                    if ($default_settlement->id == 2) {
                        if (date('H:i') >= $timeOfTransitionToNextBankingDay)
                            $start_date = date('Y-m-d', strtotime($start_date . '+1 days'));
                    }
                    break;
                } else {
                    $start_date = date('Y-m-d', strtotime($start_date . '+1 days'));
                }
            }
        }

        Orders::where(['id' => $orderId])->update(['probably_start_date' => $start_date]);

        return response($start_date, 200);
    }

    public function addCard(Request $request)
    {
        $userId = self::$userId;
        //Обязательные поля => текст ошибки
        $requiredParams = [
            'pan' => 'Номер карты обязателен к заполнению',
            'expdate' => 'Дата окончания карты обязательна к заполнению'
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


        //Проверка на дубликат
        $checkPan = Cards::checkPan($pan);

        if ($checkPan && ($checkPan->user_id != $userId))
            return response('Данный номер карты уже использовался при регистрации', 406);

        //Cбрасываем предудущий выбор карты по умолчанию
        if ($default == 1) {
            Cards::setZeroDefault($userId);
        }

        $cardData = [
            'user_id' => $userId,
            'pan' => $pan,
            'expdate' => $expdate,
            'base_card' => $default
        ];

        Cards::insert($cardData);

        Users::where('id', $userId)->update(['stage_registration' => 5]);

        return response('success', 200);
    }
}