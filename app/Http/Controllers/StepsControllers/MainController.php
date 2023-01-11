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
                return ['status' => 500, 'resp' => $value];
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
            return ['status' => 404, 'resp' => $error];

        //Счет для выплаты займа
        $settlement = OrganisationSettlement::getDefault();

        $number = Users::getLastPersonalNumber();
        $number++;

        $userData = [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'patronymic' => $patronymic,
            'birth' => $birth,
            'birth_place' => $birth_place,
            'password' => '',
            'phone_mobile' => $phone,
            'personal_number' => $number,
            'canSendOnec' => 1,
            'canSendYaDisk' => 1,
            'stage_registration' => 1
        ];

        $userId = Users::insertGetId($userData);

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
            'order_source_id' => $order_source_id
        ];

        $newOrder = new Orders($orderData);
        $newOrder->save();

        return ['status' => 200, 'resp' => 'success'];
    }
}