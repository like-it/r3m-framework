<?php
/**
 * @author          Remco van der Velde
 * @since           03-08-2022
 * @copyright       (c) Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *  -    all
 */

namespace R3m\Io\Module\Route;

use R3m\Io\App;

class TypeFloat {

    public static function validate(App $object, $string=''): bool
    {
        if(empty($string)){
            return false;
        }
        $value = $string + 0;
        if(is_float($value)){
            return true;
        }
        return false;
    }

}