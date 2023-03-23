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

use R3m\Io\App;
use R3m\Io\Exception\ObjectException;
use R3m\Io\Module\Controller;
use R3m\Io\Module\Event;

use Exception;

use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;

class Uuid extends Controller {
    const DIR = __DIR__;
    const NAME = 'Uuid';
    const INFO = '{{binary()}} uuid                           | Uuid generation';

    /**
     * @throws ObjectException
     */
    public static function run(App $object){
        $name = false;
        $url = false;
        try {
            $name = Uuid::name(__FUNCTION__    , Uuid::NAME);
            $url = Uuid::locate($object, $name);
            $response = Uuid::response($object, $url);
            Event::trigger($object, strtolower(Uuid::NAME) . '.' . __FUNCTION__, [
                'name' => $name,
                'url' => $url
            ]);
            return $response;
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            Event::trigger($object, strtolower(Uuid::NAME) . '.' . __FUNCTION__, [
                'name' => $name,
                'url' => $url,
                'exception' => $exception
            ]);
            return $exception;
        }
    }
}