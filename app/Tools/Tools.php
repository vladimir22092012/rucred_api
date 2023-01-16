<?php

namespace App\Tools;

use Illuminate\Http\Request;

abstract class Tools
{
    abstract static function processing($method, $data);
}