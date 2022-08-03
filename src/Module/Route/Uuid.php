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

class Uuid {

    public static function validate($object, $string=''): bool
    {
        //format: %s%s-%s-%s-%s-%s%s%s
        $explode = explode('-', $string);
        if (count($explode) !== 5) {
            return false;
        }
        if (strlen($explode[0]) !== 8) {
            return false;
        }
        if (strlen($explode[1]) !== 4) {
            return false;
        }
        if (strlen($explode[2]) !== 4) {
            return false;
        }
        if (strlen($explode[3]) !== 4) {
            return false;
        }
        if (strlen($explode[4]) !== 12) {
            return false;
        }
        return true;
    }

}