<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_password_hash(Parse $parse, Data $data, $password=''){
    return password_hash($password, PASSWORD_BCRYPT, [
        'cost' => 12
    ]);
}
