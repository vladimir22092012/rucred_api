<?php

namespace App\Info;

use App\Entity\Financial;
use App\Models\BankRequisite;
use App\Models\Branch;
use App\Models\Cards;
use App\Models\Companies;
use App\Models\Contracts;
use App\Models\Documents;
use App\Models\GroupsLoantypes;
use App\Models\Loantypes;
use App\Models\Orders;
use App\Models\OrganisationSettlement;
use App\Models\PaymentsSchedules;
use App\Models\ProjectContractNumber;
use App\Models\Scoring;
use App\Models\Setting;
use App\Models\Users;
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

            $annoouitet_pay = $amount;

            $payment_schedule = PaymentSchedule::processing('pdl', $data);

        } else {

            $period = $tariff->max_period;
            $data = [
                'amount' => $amount,
                'start_date' => $probably_start_date,
                'end_date' => $probably_return_date,
                'first_pay_day' => $first_pay_day,
                'percent' => $percents,
                'free_period' => $tariff->free_period,
                'min_period' => $tariff->min_period,
                'period' => $period,
            ];

            $annoouitet_pay = $amount;

            $payment_schedule = PaymentSchedule::processing('annouitet', $data);

        }

        $result = [
            'end_date' => $end_date->format('d.m.Y'),
            'return_sum' => ($tariff->type == 'pdl') ? $payment_schedule['result']['all_sum_pay'] : $probably_return_sum,
            'payment_schedule' => $payment_schedule,
            'first_pay_day' => $first_pay_day,
            'annoouitet_pay' => $annoouitet_pay,
        ];

        return response($result, 200);
    }
}
