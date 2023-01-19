<?php

namespace App\Http\Controllers\StepsControllers;

use App\Models\Contracts;
use App\Models\Loantypes;
use App\Models\Orders;
use Illuminate\Http\Request;

class ChangeLoanSettingsController extends StepsController
{
    public function action(Request $request)
    {
        //Обязательные поля => текст ошибки
        $requiredParams = [
            'amount' => 'Сумма займа обязательна к заполнению',
            'start_date' => 'Дата начала займа обязательна к заполнению',
            'tariff_id' => 'ID тарифа обязательно к заполнению',
        ];

        //Проверка на обязательные поля в запросе
        foreach ($requiredParams as $key => $value) {
            if (!isset($request[$key])) {
                return response($value, 400);
            }
        }

        $amount = $request['amount'];            //Сумма займа
        $start_date = $request['start_date'];        //дата начала займа
        $tariff_id = $request['tariff_id'];         //id тарифа(loan_type)

        $tariff = Loantypes::find($tariff_id);
        $error = false;

        if ($amount < $tariff->min_amount) {
            $error = 'Сумма займа не может быть меньше ' . $tariff->min_amount;
        }

        if ($amount > $tariff->max_amount) {
            $error = 'Сумма займа не может быть больше ' . $tariff->max_amount;
        }

        if ($error)
            return response($error, 400);

        $order = Orders::getUnfinished(self::$userId);
        $contract = Contracts::find($order->contract_id);

        $update =
            [
                'amount' => $amount,
                'loan_type' => $tariff_id,
                'probably_start_date' => date('Y-m-d H:i:s', strtotime($start_date))
            ];

        Orders::where('id', $order->id)->update($update);

        if (!empty($contract))
            Orders::where('id', $contract->id)->update(['amount' => $amount]);

        return response('success', 200);

    }
}