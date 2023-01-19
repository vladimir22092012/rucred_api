<?php

namespace App\Http\Controllers\StepsControllers;

use App\Entity\Financial;
use App\Models\BankRequisite;
use App\Models\Branch;
use App\Models\Cards;
use App\Models\Contracts;
use App\Models\Documents;
use App\Models\GroupsLoantypes;
use App\Models\Loantypes;
use App\Models\Orders;
use App\Models\PaymentsSchedules;
use App\Models\Scoring;
use App\Models\Users;
use App\Models\WeekendCalendar;
use App\Models\YaDiskCron;
use App\Tools\PaymentSchedule;
use App\Tools\Utils;
use Illuminate\Http\Request;

class ProfunionController extends StepsController
{
    public function action(Request $request)
    {
        $userId = self::$userId;

        $profunion      = $request['profunion'];      //член профсоюза
        $want_profunion = $request['want_profunion']; //желание вступить

        if ($profunion == 0 && $want_profunion == 1) {
            $profunion = 2;
        }

        $userData = [
            'profunion'          => $profunion,
            'stage_registration' => 6,
        ];

        Users::where('id', $userId)->update($userData);

        return response('success', 200);
    }
}