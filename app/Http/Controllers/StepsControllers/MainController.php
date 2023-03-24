<?php

namespace App\Http\Controllers\StepsControllers;

use App\Models\Loantypes;
use App\Models\Orders;
use App\Models\OrganisationSettlement;
use App\Models\Users;
use App\Models\UsersTokens;
use Illuminate\Http\Request;

class MainController extends StepsController
{
    public function action(Request $request)
    {

        //Обязательные поля => текст ошибки
        $requiredParams = [
            'amount' => 'Сумма займа обязательна к заполнению',
            'start_date' => 'Дата начала займа обязательна к заполнению',
            'tariff_id' => 'ID тарифа обязательно к заполнению',
            'birth' => 'Дата рождения обязательна к заполнению',
            'birth_place' => 'Место рождения обязательно к заполнению',
            'phone' => 'Телефон обязателен к заполнению',
            'firstname' => 'Имя обязательно к заполнению',
            'lastname' => 'Фамилия обязательна к заполнению',
        ];

        //Проверка на обязательные поля в запросе
        foreach ($requiredParams as $key => $value) {
            if (!isset($request[$key])) {
                return response($value, 400);
            }
        }

        $amount = $request['amount'];            //Сумма займа
        $start_date = $request['start_date'];        //дата начала займа
        $tariff_id = $request['tariff_id'];         //id тарифа(loan_type)
        $birth = $request['birth'];             //дата рождения
        $birth_place = $request['birth_place'];       //место рождения
        $phone = $request['phone'];             //мобильный телефон (поле phone_mobile в таблице users)
        $firstname = $request['firstname'];         //Имя
        $lastname = $request['lastname'];          //Фамилия
        $patronymic = $request['patronymic'] ?? '';  //Отчество
        $source = $request['source'] ?? 'site';


        $tariff = Loantypes::find($tariff_id);
        $error = false;

        if ($amount < $tariff->min_amount) {
            $error = 'Сумма займа не может быть меньше ' . $tariff->min_amount;
        }

        if ($amount > $tariff->max_amount) {
            $error = 'Сумма займа не может быть больше ' . $tariff->max_amount;
        }

        if ($error)
            return response($error, 400);

        //Счет для выплаты займа
        $settlement = OrganisationSettlement::getDefault();

        $number = Users::getLastPersonalNumber();
        $number++;

        //Проверка на 6 знаков персонального номера
        $number = str_split($number);

        if (count($number) > 6)
            $number = Users::getFreePersonalNumber();
        else
            $number = implode($number);

        $userData = [
            'firstname' => mb_strtoupper($firstname),
            'lastname' => mb_strtoupper($lastname),
            'patronymic' => mb_strtoupper($patronymic),
            'birth' => $birth,
            'birth_place' => $birth_place,
            'password' => '',
            'phone_mobile' => $phone,
            'personal_number' => $number,
            'canSendOnec' => 1,
            'canSendYaDisk' => 1,
            'stage_registration' => 1
        ];

        $existUser = Users::where('phone_mobile', $phone)->first();

        if (!empty($existUser)) {
            $userId = $existUser->id;

            $userData = [
                'firstname' => mb_strtoupper($firstname),
                'lastname' => mb_strtoupper($lastname),
                'patronymic' => mb_strtoupper($patronymic),
                'birth' => $birth,
                'birth_place' => $birth_place,
                'password' => '',
                'phone_mobile' => $phone,
                'stage_registration' => 1
            ];

            Users::where('id', $existUser->id)->update($userData);
        } else {
            $userId = Users::insertGetId($userData);
        }

        UsersTokens::where('token', $request->header('Authorization'))->update(['user_id' => $userId]);

        $order_source_id = 1;

        if ($source == 'mobile') {
            $order_source_id = 2;
        }

        $orderData = [
            'amount' => $amount,
            'date' => date('Y-m-d H:i:s'),
            'user_id' => $userId,
            'status' => 12,
            'offline' => 0,
            'charge' => 0.00,
            'insure' => 0.00,
            'loan_type' => (int)$tariff_id,
            'probably_start_date' => $start_date,
            'settlement_id' => $settlement->id,
            'order_source_id' => $order_source_id,
            'first_loan' => 1
        ];

        $newOrder = new Orders($orderData);
        $newOrder->save();

        return response('success', 200);
    }
}
