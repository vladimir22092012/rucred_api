<?php

namespace App\Http\Controllers\RepeatLoans;

use App\Models\Companies;
use App\Models\Group;
use App\Models\Orders;
use App\Models\OrganisationSettlement;
use App\Models\Users;
use Illuminate\Http\Request;

class LoanSettingsController extends RepeatLoansController
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

        $user = Users::find(self::$userId);

        $amount = $request['amount'];            //Сумма займа
        $start_date = $request['start_date'];        //дата начала займа
        $tariff_id = $request['tariff_id'];         //id тарифа(loan_type)
        $source = $request['source'] ?? 'site';

        $order_source_id = 1;

        if ($source == 'mobile') {
            $order_source_id = 2;
        }

        $settlement = OrganisationSettlement::getDefault();

        $group = Group::find($user->group_id);
        $company = Companies::find($user->company_id);

        $order_uid = $group->number . "$company->number $user->personal_number";

        $orderData = [
            'amount' => $amount,
            'date' => date('Y-m-d H:i:s'),
            'offline' => 0,
            'charge' => 0.00,
            'insure' => 0.00,
            'loan_type' => (int)$tariff_id,
            'probably_start_date' => $start_date,
            'settlement_id' => $settlement->id,
            'order_source_id' => $order_source_id,
            'group_id'   => $user->group_id,
            'company_id' => $user->company_id,
            'branche_id' => $user->branch_id,
            'uid'        => $order_uid,
        ];

        Orders::updateOrCreate(['user_id' => self::$userId, 'status' => 12, 'is_archived' => 0], $orderData);

        Users::where('id', self::$userId)->update(['stage_registration' => 4]);

        return response('success', 200);
    }
}