<?php

namespace App\Http\Controllers\RepeatLoans;

use App\Entity\Financial;
use App\Models\AspCode;
use App\Models\BankRequisite;
use App\Models\Branch;
use App\Models\Cards;
use App\Models\CommunicationTheme;
use App\Models\Companies;
use App\Models\Contracts;
use App\Models\Documents;
use App\Models\Group;
use App\Models\GroupsLoantypes;
use App\Models\Loantypes;
use App\Models\NotificationCron;
use App\Models\Orders;
use App\Models\OrganisationSettlement;
use App\Models\PaymentsSchedules;
use App\Models\ProjectContractNumber;
use App\Models\Scoring;
use App\Models\SmsMessages;
use App\Models\Ticket;
use App\Models\TicketsMessages;
use App\Models\Users;
use App\Models\WeekendCalendar;
use App\Models\YaDiskCron;
use App\Tools\PaymentSchedule;
use App\Tools\Utils;
use Illuminate\Http\Request;

class PopUpController extends RepeatLoansController
{
    public function action(Request $request)
    {

        //Обязательные поля => текст ошибки
        $requiredParams = [
            'tariff_id' => 'ID тарифа обязательно к заполнению',
            'amount' => 'Сумма займа обязательно к заполнению',
            'income' => 'Средний доход обязательно к заполнению',
            'expenses' => 'Средний расход обязательно к заполнению',
            'company_id' => 'Работодатель обязательно к заполнению',
            'profunion' => 'Просфоюз обязательно к заполнению',
            'code' => 'Проверочный код обязательно к заполнению'
        ];

        //Проверка на обязательные поля в запросе
        foreach ($requiredParams as $key => $value) {
            if (!isset($request[$key])) {
                return response($value, 400);
            }
        }

        $userId = $request['user_id'];
        $tariffId = $request['tariff_id'];
        $amount = $request['amount'];
        $income = $request['income'];
        $expenses = $request['expenses'];
        $dependents = $request['dependents'];
        $companyId = $request['company_id'];
        $profunion = $request['profunion'];
        $code = $request['code'];
        $source = $request['source'] ?? 'site';

        $company = Companies::find($companyId);

        $user = Users::find($userId);

        $sendCode = SmsMessages::where('phone', $user->phone_mobile)->latest('id')->first();

        if ($sendCode->code != $code)
            return response('Введеный код не совпадает с отправленным', 406);

        $tariff = Loantypes::find($tariffId);
        $branch = Branch::getDefault($companyId, $company->group_id);

        $startDate = date('Y-m-d');
        $first_pay = new \DateTime(date('Y-m-' . $branch->payday, strtotime($startDate)));
        $end_date = date('Y-m-' . $branch->payday, strtotime($startDate . '+' . $tariff->max_period . 'month'));

        $start_date = new \DateTime($startDate);
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

        $orderPeriod = date_diff($start_date, $end_date)->days;
        $period = $tariff->max_period;

        $percentsGroup = GroupsLoantypes::getPercents($company->group_id, $tariffId);
        $percents = $percentsGroup->standart_percents;

        if ($profunion == 1) {
            $percents = $percentsGroup->preferential_percents;
        }

        $data = [
            'amount' => $amount,
            'start_date' => $start_date->format('Y-m-d H:i:s'),
            'end_date' => $end_date->format('Y-m-d H:i:s'),
            'first_pay_day' => $branch->payday,
            'percent' => $percents,
            'free_period' => $tariff->free_period,
            'min_period' => $tariff->min_period,
            'period' => $period
        ];

        if ($tariff->type == 'pdl') {
            $payment_schedule = PaymentSchedule::processing('pdl',$data);
        } else {
            $payment_schedule = PaymentSchedule::processing('annouitet', $data);
        }

        $percent_per_month = (($percents / 100) * 365) / 12;

        $probably_return_sum = $amount * ($percent_per_month / (1 - pow((1 + $percent_per_month), -$period)));

        $bankRequisite = BankRequisite::getDefault($userId);
        $card = Cards::getDefault($userId);

        //математические формулы и расчеты
        $financial = new Financial();

        $dates = [];
        $payments = [];

        $dates[0] = date('d.m.Y', strtotime($start_date->format('Y-m-d')));
        $payments[0] = -$amount;

        foreach ($payment_schedule as $date => $pay) {
            if ($date != 'result') {
                $payments[] = round($pay['pay_sum'], 2);
                $dates[] = date('d.m.Y', strtotime($date));
            }
        }

        foreach ($dates as $date) {
            $date = new \DateTime(date('Y-m-d', strtotime($date)));

            $new_dates[] = mktime(
                (int)$date->format('H'),
                (int)$date->format('i'),
                (int)$date->format('s'),
                (int)$date->format('m'),
                (int)$date->format('d'),
                (int)$date->format('Y')
            );
        }

        $xirr = round($financial->XIRR($payments, $new_dates) * 100, 3);

        $xirr /= 100;
        $psk = round(((pow((1 + $xirr), (1 / 12)) - 1) * 12) * 100, 3);

        $month_pay = $amount * ((1 / $tariff->max_period) + (($psk / 100) / 12));

        $pdn = round(($month_pay / $user->income) * 100, 2);

        $userData = [
            'profunion' => $profunion,
            'stage_registration' => 8,
            'pdn' => $pdn,
            'income' => $income,
            'expenses' => $expenses,
            'dependents' => $dependents,
            'company_id' => $companyId,
            'group_id' => $company->group_id,
            'branche_id' => $branch->id
        ];

        Users::updateOrCreate(
            ['id' => $userId],
            $userData
        );

        $settlement = OrganisationSettlement::getDefault();

        $order_source_id = 1;
        if ($source == 'mobile') {
            $order_source_id = 2;
        }

        $group = Group::find($company->group_id);

        $order_uid = $group->number . "$company->number $user->personal_number";

        $orderData = [
            'amount' => $amount,
            'user_id' => $userId,
            'date' => date('Y-m-d H:i:s'),
            'status' => 0,
            'offline' => 0,
            'charge' => 0.00,
            'insure' => 0.00,
            'loan_type' => $tariffId,
            'probably_start_date' => $start_date,
            'settlement_id' => $settlement->id,
            'order_source_id' => $order_source_id,
            'probably_return_date' => $end_date->format('Y-m-d H:i:s'),
            'probably_return_sum' => $probably_return_sum,
            'period' => $orderPeriod,
            'percent' => $percents,
            'requisite_id' => ($bankRequisite) ? $bankRequisite->id : null,
            'card_id' => ($card) ? $card->id : null,
            'uid' => $order_uid,
            'company_id' => $companyId,
            'group_id' => $group->id,
            'branche_id' => $branch->id
        ];

        $newOrder = new Orders($orderData);
        $newOrder->save();
        $orderId = $newOrder->id;

        $payData = [
            'actual' => 1,
            'schedule' => json_encode($payment_schedule),
            'psk' => $psk,
            'comment' => 'Первый график'
        ];

        PaymentsSchedules::updateOrCreate(
            ['user_id' => $userId, 'order_id' => $orderId, 'type' => 'first'],
            $payData
        );

        $uid = rand(000000000, 999999999);

        $aspData = [
            'user_id' => $userId,
            'order_id' => $orderId,
            'code' => $code,
            'recepient' => $user->phone_mobile,
            'manager_id' => 0,
            'type' => 'sms',
            'created' => date('Y-m-d H:i:s'),
            'uid' => $uid
        ];

        $asp = new AspCode($aspData);
        $asp->save();

        //Запускаем скоринг
        Scoring::addScorings($userId, $orderId);

        //Создание контракта
        $number = $order_uid;
        $number = explode(' ', $number);
        $count_contracts = Contracts::where('user_id', $userId)->count();
        $count_contracts++;

        $count_contracts = str_pad("$count_contracts", 2, '0', STR_PAD_LEFT);

        $projectNumber = "$number[0] $tariff->number $number[1] $count_contracts";

        ProjectContractNumber::updateOrCreate(['orderId' => $orderId, 'userId' => $userId], ['uid' => $projectNumber]);

        $contractData = [
            'number' => $projectNumber,
            'amount' => $amount,
            'period' => $orderPeriod,
            'base_percent' => $percents,
            'status' => 0,
            'loan_body_summ' => $amount,
            'loan_percents_summ' => 0,
            'loan_peni_summ' => 0
        ];
        $contract = Contracts::updateOrCreate(
            ['user_id' => $userId, 'order_id' => $orderId],
            $contractData
        );

        Orders::updateOrCreate(
            ['id' => $orderId],
            ['contract_id' => $contract->id]
        );

        $cron =
            [
                'order_id' => $orderId,
                'pak' => 'first_pak',
                'online' => 1
            ];

        YaDiskCron::insert($cron);

        Documents::createDocsForRegistration($userId, $orderId);
        Documents::createDocsAfterRegistrarion($userId, $orderId);
        Documents::createDocsEndRegistrarion($userId, $orderId);

        $communicationTheme = CommunicationTheme::find(18);
        $ticket = [
            'creator'           => 0,
            //'creator_company'   => 2,
            'client_lastname'   => $user->lastname,
            'client_firstname'  => $user->firstname,
            'client_patronymic' => $user->patronymic,
            'head'              => $communicationTheme->head,
            'text'              => $communicationTheme->text,
            'theme_id'          => 18,
            'company_id'        => $companyId,
            'group_id'          => 2,//$order->group_id,
            'order_id'          => $orderId,
            'status'            => 0
        ];

        $tiketId = Ticket::insertGetId($ticket);

        //Сообщение в тикет
        $message =
            [
                'message'    => $communicationTheme->text,
                'ticket_id'  => $tiketId,
                'manager_id' => 0
            ];

        TicketsMessages::insertGetId($message);

        //Добавляем в расписание крон
        $cron = [
            'ticket_id'    => $tiketId,
            'is_complited' => 0
        ];
        NotificationCron::insert($cron);

        return response('success', 200);
    }
}