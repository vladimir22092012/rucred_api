<?php

namespace App\Info;

use App\Models\Companies;
use App\Models\Group;
use App\Models\GroupsLoantypes;
use App\Models\Loantypes;
use Illuminate\Http\Request;

class Employers extends Info
{

    static function get(Request $request)
    {
        $groups = Group::whereIn('blocked', ['all', 'online'])->get();
        $result = [];

        foreach ($groups as $key => $group) {
            $result[$key]['group_id'] = $group->id;
            $result[$key]['name'] = $group->name;
            $companies = $group->companies()->get();
            $compArr = [];
            foreach ($companies as $keyCom => $company) {
                if($company->blocked == 1 || !in_array($company->permissions, ['all', 'online']))
                {
                    unset($companies[$keyCom]);
                    continue;
                }

                $compArr[$keyCom]['company_id'] = $company->id;
                $compArr[$keyCom]['name'] = $company->name;
                $branches = $company->branches()->get();
                $baranchArr = [];
                foreach ($branches as $keyBra => $branch) {
                    $baranchArr[$keyBra]['branch_id'] = $branch->id;
                    $baranchArr[$keyBra]['name'] = $branch->name;
                }
                $compArr[$keyCom]['branches'] = $baranchArr;
            }
            $result[$key]['companies'] = $compArr;
        }

        return response($result, 200);
    }

    static function change(Request $request)
    {
        $company_id = $request['group_id'];

        if(empty($company_id))
            return response('Отсутствует параметр group_id', 400);

        $company = Companies::where('id', $company_id)->first();

        $group_loantypes = GroupsLoantypes::where(['group_id' => $company->group_id, 'on_off_flag' => 1])->get();

        if (!empty($group_loantypes)) {
            foreach ($group_loantypes as $group_loantype) {
                $loantype = Loantypes::where('id', $group_loantype->loantype_id)->first();

                $actual_percents = GroupsLoantypes::getPercents($group_loantype->group_id, $group_loantype->loantype_id);
                $loantype->percent = $actual_percents->standart_percents;
                $loantype->profunion = $actual_percents->preferential_percents;

                $loantypes[] = $loantype;
            }
        }

        if (!isset($loantypes))
            $loantypes = 'Нет подходящих тарифов';

        return response($loantypes, 200);
    }
}