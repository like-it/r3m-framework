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

use stdClass;

use R3m\Io\App;
use R3m\Io\Config;

use R3m\Io\Module\Autoload;
use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;
use R3m\Io\Module\Parse;


use Exception;
use R3m\Io\Exception\PluginNotFoundException;
use R3m\Io\Exception\PluginNotAllowedException;
use R3m\Io\Exception\FileWriteException;
use R3m\Io\Exception\FileAppendException;
use R3m\Io\Exception\FileMoveException;

class TypeObject {

    public static function validate($object, $string=''): bool
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