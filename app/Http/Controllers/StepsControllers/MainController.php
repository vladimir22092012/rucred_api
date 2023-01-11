<?php
namespace App\Http\Controllers\StepsControllers;

use Illuminate\Http\Request;

class MainController extends StepsController
{
    public function action(Request $request)
    {
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
        $orderid = $request['orderId'] ?? '';
    }
}