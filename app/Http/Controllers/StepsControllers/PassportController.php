<?php

namespace App\Http\Controllers\StepsControllers;

use Illuminate\Http\Request;

class PassportController extends StepsController
{
    public function get(Request $request)
    {
        //Обязательные поля => текст ошибки
        $requiredParams = [
            'passport_serial'   => 'Серия паспорта обязательна к заполнению',
            'passport_number'   => 'Номер паспорта обязателен к заполнению',
            'passport_date'     => 'Дата выдачи паспорта обязательна к заполнению',
            'passport_issued'   => 'Поле Кем выдан обязательно к заполнению',
            'subdivision_code'  => 'Код подразделения обязателен к заполнению',
            'snils'             => 'СНИЛС обязателен к заполнению',
            'inn'               => 'ИНН обязателен к заполнению',
            'regadressfull'     => 'Адрес регистрации обязателен к заполнению',
        ];

        //Проверка на обязательные поля в запросе
        foreach ($requiredParams as $key => $value) {
            if (! isset($params[$key])) {
                $this->logger->info("Ошибка при регистрации, этап Passport(2) - $value -  Параметры запроса: " . json_encode($params));
                throw new HttpBadRequestException($this->request, $value);
            }
        }
    }
}
