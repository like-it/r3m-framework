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
namespace R3m\Io\Cli\Linefeed\Controller;

use R3m\Io\Module\View;
use Exception;
use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;

class Linefeed extends View {
    const DIR = __DIR__;
    const NAME = 'Linefeed';
    const INFO = '{{binary()}} linefeed                       | Linefeed';
    
    public static function run($object){
        try {
            $url = $object->config('controller.dir.data') . 'Linefeed' . $object->config('extension.json');
            $config = $object->data_read($url, sha1($url));
            ddd($config);

            $name = Linefeed::name(__FUNCTION__,Linefeed::NAME);
            $url = Linefeed::locate($object, $name);
            return Linefeed::response($object, $url);
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            return $exception;
        }
    }
}