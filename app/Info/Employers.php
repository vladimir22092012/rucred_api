<?php

namespace App\Info;

use App\Models\Group;
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
}