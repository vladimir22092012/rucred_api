<?php

namespace App\Http\Controllers\RepeatLoans;

use App\Entity\Financial;
use App\Models\BankRequisite;
use App\Models\Branch;
use App\Models\Cards;
use App\Models\Contracts;
use App\Models\Documents;
use App\Models\GroupsLoantypes;
use App\Models\Loantypes;
use App\Models\Orders;
use App\Models\OrganisationSettlement;
use App\Models\PaymentsSchedules;
use App\Models\ProjectContractNumber;
use App\Models\Scoring;
use App\Models\Users;
use App\Models\WeekendCalendar;
use App\Models\YaDiskCron;
use App\Tools\PaymentSchedule;
use App\Tools\Utils;
use Illuminate\Http\Request;

class LastStepController extends RepeatLoansController
{
    public function action(Request $request)
    {
        $userId = self::$userId;

        $user = Users::find($userId);
        $profunion = $user->profunion;

        //Расчет данных для ордера(с учетом профсоюза)
        $order = Orders::getUnfinished($userId);
        $branch_id = $order->branche_id;
        $branch = Branch::find($branch_id);

        $first_pay_day = $branch->payday;
        $start_date = $order->probably_start_date;
        $amount = $order->amount;
        $tariff_id = $order->loan_type;
        $group_id = $order->group_id;

        $tariff = Loantypes::find($tariff_id);

        $start_date = date('Y-m-d', strtotime($start_date));
        $first_pay = new \DateTime(date('Y-m-' . $first_pay_day, strtotime($start_date)));
        $end_date = date('Y-m-' . $first_pay_day, strtotime($start_date . '+' . $tariff->max_period . 'month'));

        $start_date = new \DateTime($start_date);
        $end_date = new \DateTime($end_date);

        $start_date = Utils::processing('check_pay_date', $start_date);

        if ($start_date > $first_pay) {
            $first_pay->add(new \DateInterval('P1M'));
        }

        for ($i = 0; $i <= 15; $i++) {
            $check_date = WeekendCalendar::checkDate($end_date->format('Y-m-d'));

            if ($check_date == null) {
                break;
            } else {
                $end_date->sub(new \DateInterval('P1D'));
            }
        }

        $orderPeriod = date_diff($start_date, $end_date)->days;
        $period = $tariff->max_period;

        $percentsGroup = GroupsLoantypes::getPercents($group_id, $tariff_id);
        $percents = $percentsGroup->standart_percents;

        if ($profunion == 1) {
            $percents = $percentsGroup->preferential_percents;
        }

        $data = [
            'amount' => $amount,
            'start_date' => $start_date->format('Y-m-d H:i:s'),
            'end_date' => $end_date->format('Y-m-d H:i:s'),
            'first_pay_day' => $first_pay_day,
            'percent' => $percents,
            'free_period' => $tariff->free_period,
            'min_period' => $tariff->min_period,
            'period' => $period,
            'order_id' => $order->id
        ];

        if ($tariff->type == 'pdl') {
            $payment_schedule = PaymentSchedule::processing('pdl', $data);
        } else {
            $payment_schedule = PaymentSchedule::processing('annouitet', $data);
        }

        $percent_per_month = (($percents / 100) * 365) / 12;

        $probably_return_sum = $amount * ($percent_per_month / (1 - pow((1 + $percent_per_month), -$period)));

        $bankRequisite = BankRequisite::getDefault($userId);
        $card = Cards::getDefault($userId);

        $orderData = [
            'probably_return_sum' => $probably_return_sum,
            'period' => $orderPeriod,
            'percent' => $percents,
            'status' => 12,
            'requisite_id' => ($bankRequisite) ? $bankRequisite->id : null,
            'card_id' => ($card) ? $card->id : null,
        ];


        //математические формулы и расчеты
        $financial = new Financial();

        $dates = [];
        $payments = [];

        $dates[0] = date('d.m.Y', strtotime($order->probably_start_date));
        $payments[0] = -$amount;

        foreach ($payment_schedule as $date => $pay) {
            if ($date != 'result') {
                $payments[] = round($pay['pay_sum'], 2);
                $dates[] = date('d.m.Y', strtotime($date));
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

        $xirr = round($financial->XIRR($payments, $new_dates) * 100, 3);

        $xirr /= 100;
        $psk = round(((pow((1 + $xirr), (1 / 12)) - 1) * 12) * 100, 3);

        $month_pay = $amount * ((1 / $tariff->max_period) + (($psk / 100) / 12));

        $user = Users::find($userId);

        $pdn = round(($month_pay / $user->income) * 100, 2);

        $userData = [
            'profunion' => $profunion,
            'pdn' => $pdn
        ];

        Users::where('id', $userId)->update($userData);

        $payData = [
            'actual' => 1,
            'schedule' => json_encode($payment_schedule),
            'psk' => $psk,
            'comment' => 'Первый график'
        ];

        PaymentsSchedules::updateOrCreate(
            ['user_id' => $userId, 'order_id' => $order->id, 'type' => 'first'],
            $payData
        );

        //Сохраняем расчеты по ордеру
        Orders::where('id', $order->id)->update($orderData);

        //todo: переместить в конец регистрации BEGIN
        //Запускаем скоринг
        Scoring::addScorings($userId, $order->id);

        //Создание контракта
        $number = $order->uid;
        $number = explode(' ', $number);
        $count_contracts = Contracts::where('user_id', $userId)->whereIn('status', [2, 3, 4])->count();

        if (!empty($count_contracts)) {
            $count_contracts = str_pad($count_contracts + 1, 2, '0', STR_PAD_LEFT);
        } else {
            $count_contracts = '01';
        }

        $new_number = "$number[0] $tariff->number $number[1] $count_contracts";

        ProjectContractNumber::updateOrCreate(['orderId' => $order->id, 'userId' => $userId], ['uid' => $new_number]);

        $contractData = [
            'number' => $new_number,
            'amount' => $order->amount,
            'period' => $orderPeriod,
            'base_percent' => $percents,
            'status' => 0,
            'loan_body_summ' => $order->amount,
            'loan_percents_summ' => 0,
            'loan_peni_summ' => 0
        ];
        $contract = Contracts::updateOrCreate(
            ['user_id' => $userId, 'order_id' => $order->id],
            $contractData
        );

        Orders::updateOrCreate(['id' => $order->id], ['contract_id' => $contract->id]);

        Documents::where('order_id', $order->id)->delete();

        $types = array(
            'SOGLASIE_NA_KRED_OTCHET',
            'SOGLASIE_NA_OBR_PERS_DANNIH',
            'SOGLASIE_RABOTODATEL',
            'SOGLASIE_RUKRED_RABOTODATEL',
            'ZAYAVLENIE_NA_PERECHISL_CHASTI_ZP',
            'ZAYAVLENIE_ZP_V_SCHET_POGASHENIYA_MKR',
            'INDIVIDUALNIE_USLOVIA_ONL',
            'GRAFIK_OBSL_MKR',
            'PERECHISLENIE_ZAEMN_SREDSTV',
            'OBSHIE_USLOVIYA'
        );

        $defaultSettlement = OrganisationSettlement::getDefault();

        if ($defaultSettlement->id == 2)
            $types[] = 'SOGLASIE_MINB';
        else
            $types[] = 'SOGLASIE_RDB';

        foreach ($types as $type) {
            $params = $order->toArray();
            $arrUser = $user->toArray();

            foreach ($arrUser as $key => $value) {
                if ($key == 'email') {
                    continue;
                }
                $params[$key] = $value;
            }

            $params['payment_schedule'] = PaymentsSchedules::where('order_id', $order->id)->where('actual', 1)->first()->toArray();

            $data = [
                'contract_id' => $order->contract_id,
                'name' => Documents::$names[$type],
                'template' => Documents::$templates[$type],
                'client_visible' => Documents::$client_visible[$type],
                'params' => serialize((object)$params),
                'created' => date('Y-m-d H:i:s'),
                'numeration' => Documents::$numeration[$type],
                'hash' => sha1(rand(11111, 99999))
            ];

            Documents::updateOrCreate(
                ['user_id' => $user->id, 'order_id' => $order->id, 'type' => $type],
                $data
            );
        }

        $docs = Documents::where('order_id', $order->id)->get()->toArray();
        $res = [];

        $sort =
            [
                '04.05',
                '04.06',
                '03.03',
            ];

        if ($order->settlement_id == 2)
            $sort[] = '04.05.1';
        else
            $sort[] = '04.05.2';

        $sort[] = '04.07';
        $sort[] = '04.09';
        $sort[] = '04.03.1';
        $sort[] = '04.04';
        $sort[] = '03.04';
        $sort[] = '04.12';
        $sort[] = '04.10';

        foreach ($docs as $key => $doc) {
            foreach ($sort as $k => $number) {
                if ($doc['numeration'] == $number) {
                    $res[$k] = [
                        'name' => $doc['name'],
                        'link' => env('URL_CRM') . 'online_docs?id=' . $doc['hash']
                    ];
                }
            }
        }

        return response($res, 200);
    }
}