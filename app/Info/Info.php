<?php

namespace App\Info;

use Illuminate\Http\Request;

abstract class Info
{
    abstract static function get(Request $request);
}