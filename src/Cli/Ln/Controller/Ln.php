<?php
/**
 * @author          Remco van der Velde
 * @since           04-01-2019
 * @copyright       (c) Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *  -    all
 */
namespace R3m\Io\Cli\Ln\Controller;

use R3m\Io\App;
use R3m\Io\Module\Controller;

use Exception;

use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;

class Ln extends Controller {
    const DIR = __DIR__;
    const NAME = 'Ln';
    const INFO = '{{binary()}} ln                             | ln creates a symlink if not exist';
    
    public static function run(App $object){
        try {
            $name = Ln::name(__FUNCTION__    , Ln::NAME);
            $url = Ln::locate($object, $name);
            return Ln::response($object, $url);
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            return $exception;
        }
    }
}