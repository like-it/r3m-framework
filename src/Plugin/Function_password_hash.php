<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-13
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

use Exception;
use R3m\Io\Exception\ErrorException;

function function_password_hash(Parse $parse, Data $data, $password='', $cost=13){
    try {
        $result = password_hash($password, PASSWORD_BCRYPT, [
            'cost' => $cost
        ]);
    } catch (Exception | ErrorException $exception){
        return $exception->getMessage() . "\n";
    }

    return $result;
}
