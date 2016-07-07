<?php

use Pazuzu156\IRC\Utils\Env;

if(!function_exists('env'))
{
    function env($key)
    {
        $env = new Env;
        return $env->get(strtoupper($key));
    }
}