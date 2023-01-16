<?php

namespace App\Info;

use App\Models\Branch;
use App\Models\Companies;
use App\Models\GroupsLoantypes;
use App\Models\Loantypes;
use App\Models\WeekendCalendar;
use App\Tools\PaymentSchedule;
use App\Tools\Utils;
use Illuminate\Http\Request;

class Calculator extends Info
{

    static function get(Request $request)
    {

        //Обязательные поля => текст ошибки
        $requiredParams = [
            'tariff_id' => 'ID Тарифа обязателен к заполнению',
            'start_date' => 'Дата начала займа обязательна к заполнению',
            'amount' => 'Сумма займа обязательна к заполнению',
        ];

        //Проверка на обязательные поля в запросе
        foreach ($requiredParams as $key => $value) {
            if (!isset($request[$key])) {
                return response($value, 400);
            }
        }

        $tariff_id = $request['tariff_id'];
        $amount = $request['amount'];
        $start_date = $request['start_date'];
        $profunion = $request['profunion'] ?? 0;
        $company_id = $request['company_id'] ?? false;

        $amount = preg_replace("/[^,.0-9]/", '', $amount);

        if ($company_id) {
            $company = Companies::find($company_id);
            $group = $company->group()->first();
            $group_id = $group->id;
            $branch = Branch::getDefault($company_id, $group_id);
            $branch_id = $branch->id;

            $branch = Branch::find($branch_id);
            $first_pay_day = $branch->payday;
        } else {
            $first_pay_day = 15;
        }


        $tariff = Loantypes::find($tariff_id);

        $error = false;

        if ($amount < $tariff->min_amount) {
            $error = 'Сумма займа не может быть меньше ' . $tariff->min_amount;
        }

        if ($amount > $tariff->max_amount) {
            $error = 'Сумма займа не может быть больше ' . $tariff->max_amount;
        }

        if ($error) {
            return response($error, 406);
        }

        //Расчет процентов в зависимости от полученых параметров
        //Профсоюз и компания по умолчанию
        $percents = $tariff->percent;

        //Есть профсоюз, компанию по умолчанию
        if ($profunion) {
            $percents = $tariff->profunion;
        }
        //Есть компания, профсоюз не задан
        if ($company_id) {
            $percentsGroup = GroupsLoantypes::getPercents($group_id, $tariff_id);
            $percents = $percentsGroup->standart_percents;
        }
        //Есть компания и профсоюз
        if ($company_id && $profunion == 1) {
            $percents = $percentsGroup->preferential_percents;
        }

        //Дата начала займа
        $start_date = date('Y-m-d', strtotime($start_date));
        $first_pay = new \DateTime(date('Y-m-' . $first_pay_day, strtotime($start_date)));
        $end_date = date('Y-m-' . $first_pay_day, strtotime($start_date . '+' . $tariff->max_period . 'month'));

        $period = $tariff->max_period;

        $percent_per_month = (($percents / 100) * 365) / 12;

        $probably_return_sum = $amount * ($percent_per_month / (1 - pow((1 + $percent_per_month), -$period)));

        $start_date = new \DateTime($start_date);
        $end_date = new \DateTime($end_date);

        if ($start_date > $first_pay) {
            $first_pay->add(new \DateInterval('P1M'));
        }

        $first_pay = Utils::processing('check_pay_date', $first_pay);

        if (date_diff($first_pay, $start_date)->days <= $tariff->min_period && $first_pay->format('m') != $start_date->format('m')) {
            $end_date->add(new \DateInterval('P1M'));
        }

        for ($i = 0; $i <= 15; $i++) {
            $check_date = WeekendCalendar::checkDate($end_date->format('Y-m-d'));

            if ($check_date == null) {
                break;
            } else {
                $end_date->sub(new \DateInterval('P1D'));
            }
        }

        $probably_start_date = $start_date->format('Y-m-d');
        $probably_return_date = $end_date->format('Y-m-d H:i:s');


        if ($tariff->type == 'pdl') {

            $data = [
                'amount' => $amount,
                'start_date' => $probably_start_date,
                'end_date' => $probably_return_date,
                'first_pay_day' => $first_pay_day,
                'percent' => $percents,
                'free_period' => $tariff->free_period,
                'min_period' => $tariff->min_period,
            ];

            $payment_schedule = PaymentSchedule::processing('pdl', $data);

        } else {

            //адаптированный код из црм
            $rest_sum = $amount;
            $start_date = date('Y-m-d', strtotime($probably_start_date));
            $end_date = new \DateTime(date('Y-m-' . $first_pay_day, strtotime($probably_return_date)));
            $issuance_date = new \DateTime(date('Y-m-d', strtotime($start_date)));
            $paydate = new \DateTime(date('Y-m-' . "$first_pay_day", strtotime($start_date)));

            $annoouitet_pay = $probably_return_sum;
            $annoouitet_pay = round($annoouitet_pay, 2);

            if (date('d', strtotime($start_date)) < $first_pay_day) {

                if ($issuance_date > $start_date && date_diff($paydate, $issuance_date)->days < 3) {
                    $plus_loan_percents = round(($percents / 100) * $amount * date_diff($paydate, $issuance_date)->days, 2);
                    $sum_pay = $annoouitet_pay + $plus_loan_percents;
                    $loan_percents_pay = round(($amount * $percent_per_month) + $plus_loan_percents, 2, PHP_ROUND_HALF_DOWN);
                    $body_pay = $sum_pay - $loan_percents_pay;
                    $paydate->add(new \DateInterval('P1M'));
                    $paydate = Utils::processing('check_pay_date', $paydate);
                } else {
                    $sum_pay = ($percents / 100) * $amount * date_diff($paydate, $issuance_date)->days;
                    $loan_percents_pay = $sum_pay;
                    $body_pay = 0;
                }

                $payment_schedule[$paydate->format('d.m.Y')] =
                    [
                        'pay_sum' => $sum_pay,
                        'loan_percents_pay' => $loan_percents_pay,
                        'loan_body_pay' => $body_pay,
                        'comission_pay' => 0.00,
                        'rest_pay' => $rest_sum -= $body_pay
                    ];
                $paydate->add(new \DateInterval('P1M'));

            } else {

                $issuance_date = new \DateTime(date('Y-m-d', strtotime($start_date)));
                $first_pay = new \DateTime(date('Y-m-' . $first_pay_day, strtotime($start_date . '+1 month')));
                $count_days_this_month = date('t', strtotime($issuance_date->format('Y-m-d')));
                $paydate = Utils::processing('check_pay_date', $first_pay);

                if (date_diff($first_pay, $issuance_date)->days < 20) {
                    $sum_pay = ($percents / 100) * $amount * date_diff($first_pay, $issuance_date)->days;
                    $percents_pay = $sum_pay;
                    $body_pay = 0;
                }

                if (date_diff($first_pay, $issuance_date)->days >= 20 && date_diff($first_pay, $issuance_date)->days < $count_days_this_month) {
                    $minus_percents = ($percents / 100) * $amount * ($count_days_this_month - date_diff($first_pay, $issuance_date)->days);

                    $sum_pay = $annoouitet_pay - $minus_percents;
                    $percents_pay = ($amount * $percent_per_month) - $minus_percents;
                    $body_pay = $sum_pay - $percents_pay;
                }

                if (date_diff($first_pay, $issuance_date)->days >= $count_days_this_month) {
                    $sum_pay = $annoouitet_pay;
                    $percents_pay = $amount * $percent_per_month;
                    $body_pay = $sum_pay - $percents_pay;
                }

                $payment_schedule[$paydate->format('d.m.Y')] =
                    [
                        'pay_sum' => $sum_pay,
                        'loan_percents_pay' => $percents_pay,
                        'loan_body_pay' => ($body_pay) ? $body_pay : 0,
                        'comission_pay' => 0.00,
                        'rest_pay' => $rest_sum -= $body_pay
                    ];

                $paydate->add(new \DateInterval('P1M'));

            }

            if ($rest_sum != 0) {
                $paydate->setDate($paydate->format('Y'), $paydate->format('m'), $first_pay_day);
                $interval = new \DateInterval('P1M');
                $lastdate = clone $end_date;
                $end_date->setTime(0, 0, 1);
                $daterange = new \DatePeriod($paydate, $interval, $end_date);

                foreach ($daterange as $date) {
                    $date = Utils::processing('check_pay_date', $date);

                    if ($date->format('m') == $lastdate->format('m')) {

                        $loan_body_pay = $rest_sum;
                        $loan_percents_pay = $annoouitet_pay - $loan_body_pay;
                        $rest_sum = 0.00;

                    } else {

                        $loan_percents_pay = round($rest_sum * $percent_per_month, 2);
                        $loan_body_pay = round($annoouitet_pay - $loan_percents_pay, 2);
                        $rest_sum = round($rest_sum - $loan_body_pay, 2);

                    }

                    $payment_schedule[$date->format('d.m.Y')] =
                        [
                            'pay_sum' => $annoouitet_pay,
                            'loan_percents_pay' => $loan_percents_pay,
                            'loan_body_pay' => $loan_body_pay,
                            'comission_pay' => 0.00,
                            'rest_pay' => $rest_sum
                        ];
                }
            }

            $payment_schedule['result'] =
                [
                    'all_sum_pay' => 0.00,
                    'all_loan_percents_pay' => 0.00,
                    'all_loan_body_pay' => 0.00,
                    'all_comission_pay' => 0.00,
                    'all_rest_pay_sum' => 0.00
                ];

            $dates[0] = date('d.m.Y', strtotime($probably_start_date));
            $payments[0] = -$amount;

            foreach ($payment_schedule as $date => $pay) {
                if ($date != 'result') {
                    $payments[] = round($pay['pay_sum'], 2);
                    $dates[] = date('d.m.Y', strtotime($date));
                    $payment_schedule['result']['all_sum_pay'] += round($pay['pay_sum'], 2);
                    $payment_schedule['result']['all_loan_percents_pay'] += round($pay['loan_percents_pay'], 2);
                    $payment_schedule['result']['all_loan_body_pay'] += round($pay['loan_body_pay'], 2);
                    $payment_schedule['result']['all_comission_pay'] += round($pay['comission_pay'], 2);
                    $payment_schedule['result']['all_rest_pay_sum'] = 0.00;
                }
            }

        }

        $result = [
            'end_date' => $end_date->format('d.m.Y'),
            'return_sum' => ($tariff->type == 'pdl') ? $payment_schedule['result']['all_sum_pay'] : $probably_return_sum,
            'payment_schedule' => $payment_schedule
        ];

        return response($result, 200);
    }
}