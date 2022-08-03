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

class TypeObject {

    public static function validate(App $object, $string=''): bool
    {
        if(
            substr($string, 0, 1) == '{' &&
            substr($string, -1, 1) == '}'
        ){
            $onject = json_decode($string);
            if(is_array($object)){
                return true;
            }
        }
        return false;
    }

}