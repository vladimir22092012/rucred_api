<?php

namespace App\Http\Controllers\AccountControllers;

use App\Models\Contracts;
use App\Models\Loantypes;
use App\Models\Operations;
use App\Models\Orders;
use App\Models\PaymentsSchedules;

class ActiveLoansController extends AccountController
{
    public function get()
    {
        $orders = Orders::getActiveOrders(self::$userId);

        if(empty($orders))
            return ['status' => 404, 'resp' => 'Нет активных займов'];

        $res = [];

        foreach ($orders as $order) {
            $payment_schedule = PaymentsSchedules::getSchedule($order->id);
            $payment_schedule = $payment_schedule->schedule;
            $payment_schedule = json_decode($payment_schedule, true);

            if ($order->contract_id) {
                $type = 'Микрозайм';
                $contract = Contracts::where('id', $order->contract_id)->first();
                $orderNumber = $contract->number;

                if (!in_array($contract->status, [2, 3])) {
                    $type = 'Заявка';
                    $orderNumber = $order->uid;
                }
            } else {
                $type = 'Заявка';
                $orderNumber = $order->uid;
            }

            $payments = Operations::getOperations($order->id);   //Список операций\платежей

            $totalAmount = 0;    //Сумма платежей по телу займа
            $totalProcentAmount = 0;    //Сумма платежей по процентам

            $resPayments = [];

            $j = 0; // Количество платежей по телу займа

            foreach ($payments as $payment) {
                if ($payment->type == 'PAY') {
                    $j++;
                }

                $resPayments[] = [
                    'amount' => $payment->amount,
                    'type' => $payment->type,
                    'date' => $payment->created,    //todo: Поправить на дату отправки
                    'status' => $payment->sent_status //todo: Поправить письменное значение
                ];
            }


            $schedule = $payment_schedule;

            $obl_procent_amount = round($schedule['result']['all_loan_percents_pay'], 2, PHP_ROUND_HALF_DOWN);


            unset($schedule['result']);

            $prepeaSchedule = [];
            $i = 0;
            $monthPay = 0;

            foreach ($schedule as $key => $value) {

                if ($i == 1)
                    $monthPay = $value['pay_sum'];

                $prepeaSchedule[strtotime($key)] = $value;
                $prepeaSchedule[strtotime($key)]['date'] = $key;

                $i++;
            }

            $k = 0;
            $nextPay = false; //Следующий платеж

            foreach ($prepeaSchedule as $key => $value) {
                if ($j == $k) {
                    $nextPay = $value;
                }
                if ($j > $k) {
                    $totalAmount += $value['loan_body_pay'];
                    $totalProcentAmount += $value['loan_percents_pay'];
                }
                $k++;
            }

            $paid = $totalAmount + $totalProcentAmount; //Заплачено на текущий момент
            $amount = $order->amount;  //Тело займа
            $percents = $order->percent; //Процент по текущему займу

            $start_date = $order->probably_start_date;
            $start_date = date('Y-m-d', strtotime($start_date));
            $start_date = new \DateTime($start_date);

            $now = new \DateTime();

            $totalProcentAmountMustBePaid = ($percents / 100) * $amount * date_diff($start_date, $now)->days; //Проценты на текущий день
            $totalAmountMustBePaid = $amount + $totalProcentAmountMustBePaid; //Должно быть заплачено на текущий день (для закрытия займа)

            if ($paid >= $totalAmountMustBePaid) {
                $nextPay = false;
            }

            if ($nextPay) {
                $current_payment = [
                    'amount' => round($nextPay['pay_sum'], 2, PHP_ROUND_HALF_DOWN),
                    'date' => date('d.m.Y', strtotime($nextPay['date'])),
                    'main_amount' => round($nextPay['loan_body_pay'], 2, PHP_ROUND_HALF_DOWN),
                    'procent_amount' => round($nextPay['loan_percents_pay'], 2, PHP_ROUND_HALF_DOWN),
                    'comission_amount' => $nextPay['comission_pay']
                ];
            } else {
                $current_payment = [
                    'amount' => 0,
                    'date' => date('d.m.Y', time()),
                    'main_amount' => 0,
                    'procent_amount' => 0,
                    'comission_amount' => 0
                ];
            }

            $obl_amount = round($totalAmountMustBePaid - $paid, 2, PHP_ROUND_HALF_DOWN);
            $obl_date = date('d.m.Y', time());
            $obl_main_amount = round($amount - $totalAmount, 2, PHP_ROUND_HALF_DOWN);

            $loanType = Loantypes::where('id', $order->loan_type)->first();


            $obligation = [
                'amount' => $obl_amount,
                'date' => $obl_date,
                'main_amount' => $obl_main_amount,
                'procent_amount' => $obl_procent_amount,
                'comission_amount' => 0
            ];

            $res[] = [
                'id' => $order->id,
                'number' => $orderNumber,
                'type' => $type,
                'amount' => $order->amount,
                'probably_start_date' => date('d.m.Y', strtotime($order->probably_start_date)),
                'probably_return_date' => date('d.m.Y', strtotime($order->probably_return_date)),
                'loantype_name' => $loanType->name,
                'status' => $order->status,
                'payment_schedule' => $payment_schedule,
                'current_payment' => $current_payment,
                'month_pay' => $monthPay,
                'obligation' => $obligation,
                'payments' => $resPayments
            ];
        }

        return ['status' => 200, 'resp' => $res];
    }
}
