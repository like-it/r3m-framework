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
namespace R3m\Io\Cli\Uuid\Controller;

use R3m\Io\Module\View;
use Exception;
use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;

class Uuid extends View {
    const DIR = __DIR__;
    const NAME = 'Uuid';
    const INFO = '{binary()} uuid                           | Uuid generation';
    
    public static function run($object){
        try {
            $name = Uuid::name(__FUNCTION__    , Uuid::NAME);
            $url = Uuid::locate($object, $name);
            return Uuid::response($object, $url);
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            return 'Command undefined.' . PHP_EOL;;
        }
    }
}