<?php

namespace App\Tools;


use App\Models\Orders;

class PaymentSchedule extends Tools
{
    static function processing($method, $data)
    {
        return self::$method($data);
    }

    private static function pdl($data)
    {
        $amount = $data['amount'];
        $start_date = $data['start_date'];
        $end_date = $data['end_date'];
        $first_pay_day = $data['first_pay_day'];
        $percent = $data['percent'];      //параметр тарифа с учетом профсоюза
        $free_period = $data['free_period'];  //параметр тарифа
        $min_period = $data['min_period'];   //параметр тарифа
        $orderId = $data['order_id'] ?? null;

        $percent_per_month = (($percent / 100) * 360) / 12;

        $period = 1;

        $probably_return_sum = $amount * ($percent_per_month / (1 - pow((1 + $percent_per_month), -$period)));

        //адаптированный код из црм
        $rest_sum = $amount;
        $start_date = new \DateTime(date('Y-m-d', strtotime($start_date)));
        $paydate = new \DateTime(date('Y-m-' . "$first_pay_day", strtotime($start_date->format('Y-m-d'))));
        $paydate->setDate($paydate->format('Y'), $paydate->format('m'), $first_pay_day);

        if ($start_date > $paydate || date_diff($paydate, $start_date)->days <= $free_period)
            $paydate->add(new \DateInterval('P1M'));

        $annoouitet_pay = $probably_return_sum;
        $annoouitet_pay = round($annoouitet_pay, 2);

        $iteration = 0;

        $count_days_this_month = date('t', strtotime($start_date->format('Y-m-d')));

        $paydate = Utils::processing('check_pay_date', new \DateTime($paydate->format('Y-m-' . $first_pay_day)));

        if (date_diff($paydate, $start_date)->days <= $free_period) {
            $plus_loan_percents = round(($percent / 100) * $amount * date_diff($paydate, $start_date)->days, 2);
            $sum_pay = $annoouitet_pay + $plus_loan_percents;
            $loan_percents_pay = round(($rest_sum * $percent_per_month) + $plus_loan_percents, 2);
            $body_pay = $sum_pay - $loan_percents_pay;
            $paydate->add(new \DateInterval('P1M'));
            $iteration++;
        } elseif (date_diff($paydate, $start_date)->days >= $min_period && date_diff($paydate, $start_date)->days < $count_days_this_month) {
            $body_pay = $rest_sum;
            $loan_percents_pay = $amount * ($percent/100) * date_diff($paydate, $start_date)->days;
            $sum_pay = $body_pay + $loan_percents_pay;
            $iteration++;
        } elseif (date_diff($paydate, $start_date)->days >= $count_days_this_month) {
            $body_pay = $rest_sum;
            $loan_percents_pay = $amount * ($percent/100) * date_diff($paydate, $start_date)->days;
            $sum_pay = $body_pay + $loan_percents_pay;
        } else {
            $sum_pay = ($percent / 100) * $amount * date_diff($paydate, $start_date)->days;
            $loan_percents_pay = $sum_pay;
            $body_pay = 0.00;
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

        $period -= $iteration;

        if ($rest_sum != 0) {

            for ($i = 1; $i <= $period; $i++) {
                $paydate->setDate($paydate->format('Y'), $paydate->format('m'), $first_pay_day);
                $date = Utils::processing('check_pay_date', $paydate);

                if (isset($payment_schedule[$date->format('d.m.Y')])) {
                    $date = self::add_month($date->format('d.m.Y'), 2);
                    $paydate->setDate($date->format('Y'), $date->format('m'), $first_pay_day);
                    $date = Utils::processing('check_pay_date', $paydate);
                }

                $loan_body_pay = $rest_sum;
                $loan_percents_pay = $amount * ($percent / 100) * date_diff($start_date, $date)->days - $loan_percents_pay;
                $annoouitet_pay = $loan_body_pay + $loan_percents_pay;
                $rest_sum = 0.00;

                $payment_schedule[$date->format('d.m.Y')] =
                    [
                        'pay_sum' => $annoouitet_pay,
                        'loan_percents_pay' => $loan_percents_pay,
                        'loan_body_pay' => $loan_body_pay,
                        'comission_pay' => 0.00,
                        'rest_pay' => $rest_sum
                    ];

                if(!empty($orderId))
                    Orders::where('id', $orderId)->update(['probably_return_date' => date('Y-m-d H:i:s', strtotime($date->format('d.m.Y')))]);

                $paydate->add(new \DateInterval('P1M'));
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

        foreach ($payment_schedule as $date => $pay) {
            if ($date != 'result') {
                $payment_schedule['result']['all_sum_pay'] += round($pay['pay_sum'], '2');
                $payment_schedule['result']['all_loan_percents_pay'] += round($pay['loan_percents_pay'], '2');
                $payment_schedule['result']['all_loan_body_pay'] += round($pay['loan_body_pay'], 2);
                $payment_schedule['result']['all_comission_pay'] += round($pay['comission_pay'], '2');
                $payment_schedule['result']['all_rest_pay_sum'] = 0.00;
            }
        }

        return $payment_schedule;
    }

    private static function annouitet($data)
    {
        $amount = $data['amount'];
        $start_date = $data['start_date'];
        $first_pay_day = $data['first_pay_day'];
        $percent = $data['percent'];      //параметр тарифа с учетом профсоюза
        $free_period = $data['free_period'];  //параметр тарифа
        $min_period = $data['min_period'];   //параметр тарифа
        $period = $data['period'];   //параметр тарифа
        $orderId = $data['order_id'] ?? null;

        $percent_per_month = (($percent / 100) * 365) / 12;

        $probably_return_sum = $amount * ($percent_per_month / (1 - pow((1 + $percent_per_month), -$period)));

        //адаптированный код из црм
        $rest_sum = $amount;
        $start_date = new \DateTime(date('Y-m-d', strtotime($start_date)));
        $paydate = new \DateTime(date('Y-m-' . "$first_pay_day", strtotime($start_date->format('Y-m-d'))));
        $paydate->setDate($paydate->format('Y'), $paydate->format('m'), $first_pay_day);

        if ($start_date > $paydate || date_diff($paydate, $start_date)->days <= $free_period)
            $paydate->add(new \DateInterval('P1M'));

        $annoouitet_pay = $probably_return_sum;
        $annoouitet_pay = round($annoouitet_pay, 2);

        $iteration = 0;

        $count_days_this_month = date('t', strtotime($start_date->format('Y-m-d')));

        $paydate = Utils::processing('check_pay_date', new \DateTime($paydate->format('Y-m-' . $first_pay_day)));

        if (date_diff($paydate, $start_date)->days <= $free_period) {
            $plus_loan_percents = round(($percent / 100) * $amount * date_diff($paydate, $start_date)->days, 2);
            $sum_pay = $annoouitet_pay + $plus_loan_percents;
            $loan_percents_pay = round(($rest_sum * $percent_per_month) + $plus_loan_percents, 2);
            $body_pay = $sum_pay - $loan_percents_pay;
            $paydate->add(new \DateInterval('P1M'));
            $iteration++;
        } elseif (date_diff($paydate, $start_date)->days >= $min_period && date_diff($paydate, $start_date)->days < $count_days_this_month) {
            $minus_percents = ($percent / 100) * $amount * ($count_days_this_month - date_diff($paydate, $start_date)->days);
            $sum_pay = $annoouitet_pay - round($minus_percents, 2);
            $loan_percents_pay = ($rest_sum * $percent_per_month) - $minus_percents;
            $loan_percents_pay = round($loan_percents_pay, 2, PHP_ROUND_HALF_DOWN);
            $body_pay = $sum_pay - $loan_percents_pay;
            $iteration++;
        } elseif (date_diff($paydate, $start_date)->days >= $count_days_this_month) {
            $sum_pay = $annoouitet_pay;
            $loan_percents_pay = round($rest_sum * $percent_per_month, 2, PHP_ROUND_HALF_DOWN);
            $body_pay = round($sum_pay - $loan_percents_pay, 2);
            $iteration++;
        } else {
            $sum_pay = ($percent / 100) * $amount * date_diff($paydate, $start_date)->days;
            $loan_percents_pay = $sum_pay;
            $body_pay = 0.00;
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

        $period -= $iteration;

        if ($rest_sum != 0) {

            for ($i = 1; $i <= $period; $i++) {
                $paydate->setDate($paydate->format('Y'), $paydate->format('m'), $first_pay_day);
                $date = Utils::processing('check_pay_date', $paydate);

                if ($i == $period) {
                    $loan_body_pay = $rest_sum;
                    $loan_percents_pay = $annoouitet_pay - $loan_body_pay;
                    $rest_sum = 0.00;
                } else {
                    $loan_percents_pay = round($rest_sum * $percent_per_month, 2, PHP_ROUND_HALF_DOWN);
                    $loan_body_pay = round($annoouitet_pay - $loan_percents_pay, 2);
                    $rest_sum = round($rest_sum - $loan_body_pay, 2);
                }

                if (isset($payment_schedule[$date->format('d.m.Y')])) {
                    $date = self::add_month($date->format('d.m.Y'), 2);
                    $paydate->setDate($date->format('Y'), $date->format('m'), $first_pay_day);
                    $date = Utils::processing('check_pay_date', $paydate);
                }

                $payment_schedule[$date->format('d.m.Y')] =
                    [
                        'pay_sum' => $annoouitet_pay,
                        'loan_percents_pay' => $loan_percents_pay,
                        'loan_body_pay' => $loan_body_pay,
                        'comission_pay' => 0.00,
                        'rest_pay' => $rest_sum
                    ];

                if(!empty($orderId))
                    Orders::where('id', $orderId)->update(['probably_return_date' => date('Y-m-d H:i:s', strtotime($date->format('d.m.Y')))]);

                $paydate->add(new \DateInterval('P1M'));
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

        $dates[0] = date('d.m.Y', strtotime($start_date->format('Y-m-d')));
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

        foreach ($dates as $date) {
            $date = new \DateTime(date('Y-m-d H:i:s', strtotime($date)));

            $new_dates[] = mktime(
                $date->format('H'),
                $date->format('i'),
                $date->format('s'),
                $date->format('m'),
                $date->format('d'),
                $date->format('Y')
            );
        }

        return $payment_schedule;
    }

    private static function add_month($date_str, $months)
    {
        $date = new \DateTime($date_str);

        // We extract the day of the month as $start_day
        $start_day = $date->format('j');

        // We add 1 month to the given date
        $date->modify("+{$months} month");

        // We extract the day of the month again so we can compare
        $end_day = $date->format('j');

        if ($start_day != $end_day) {
            // The day of the month isn't the same anymore, so we correct the date
            $date->modify('last day of last month');
        }

        return $date;
    }
}
