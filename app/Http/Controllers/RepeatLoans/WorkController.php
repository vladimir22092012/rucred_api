<?php

namespace App\Http\Controllers\RepeatLoans;

use App\Models\Branch;
use App\Models\Companies;
use App\Models\Group;
use App\Models\Users;
use Illuminate\Http\Request;

class WorkController extends RepeatLoansController
{
    public function action(Request $request)
    {
        $userId = self::$userId;

        $requiredParams = [
            'company_id'  => 'ID компании обязателено к заполнению',
        ];

        //Проверка на обязательные поля в запросе
        foreach ($requiredParams as $key => $value) {
            if (!isset($request[$key])) {
                return response($value, 400);
            }
        }

        $profunion      = $request['profunion'];      //член профсоюза
        $want_profunion = $request['want_profunion']; //желание вступить
        $company_id     = $request['company_id'];

        if ($profunion == 0 && $want_profunion == 1) {
            $profunion = 2;
        }

        $company    = Companies::find($company_id);
        $group      = Group::find($company->group_id);
        $group_id   = $group->id;
        $branch     = Branch::getDefault($company_id, $group_id);
        $branch_id  = $branch->id;

        $userData = [
            'group_id'           => $group_id,
            'company_id'         => $company_id,
            'branche_id'         => $branch_id,
            'profunion'          => $profunion
        ];

        Users::where('id', $userId)->update($userData);

        return response('success', 200);
    }
}