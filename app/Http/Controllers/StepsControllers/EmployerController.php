<?php

namespace App\Http\Controllers\StepsControllers;

use App\Models\Branch;
use App\Models\Companies;
use App\Models\Group;
use App\Models\Orders;
use App\Models\Users;
use Illuminate\Http\Request;

class EmployerController extends StepsController
{
    public function action(Request $request)
    {
        //Обязательные поля => текст ошибки
        $requiredParams = [
            'company_id'  => 'ID компании обязателено к заполнению',
            'income'      => 'Среднемесячный доход обязателен к заполнению',
            'expenses'    => 'Среднемесячные расходы обязателены к заполнению',
        ];

        //Проверка на обязательные поля в запросе
        foreach ($requiredParams as $key => $value) {
            if (!isset($request[$key])) {
                return response($value, 400);
            }
        }

        $company_id       = $request['company_id'];
        $income           = $request['income'];                  //Доход
        $expenses         = $request['expenses'];                //Расход
        $dependents       = $request['dependents'] ?? 0;         //Количество иждивенцев
        $attestation_otb  = $request['attestation_otb'] ?? null; //Номер аттестаии ОТБ

        //Удаление лишних символов
        $income   = preg_replace("/[^,.0-9]/", '', $income);
        $expenses = preg_replace("/[^,.0-9]/", '', $expenses);

        $company    = Companies::find($company_id);
        $group      = Group::find($company->group_id);
        $group_id   = $group->id;
        $branch     = Branch::getDefault($company_id, $group_id);
        $branch_id  = $branch->id;
        $order      = Orders::getUnfinished(self::$userId);

        $user = Users::find(self::$userId);

        $order_uid = $group->number . "$company->number $user->personal_number";

        $orderData = [
            'group_id'   => $group_id,
            'company_id' => $company_id,
            'branche_id' => $branch_id,
            'uid'        => $order_uid,
        ];

        Orders::where('id', $order->id)->update($orderData);

        $userData = [
            'group_id'           => $group_id,
            'company_id'         => $company_id,
            'branche_id'         => $branch_id,
            'income'             => $income,
            'expenses'           => $expenses,
            'dependents'         => $dependents,
            'attestation_otb'    => $attestation_otb,
            'stage_registration' => 4
        ];

        Users::where('id', $user->id)->update($userData);

        return response('success', 200);
    }
}