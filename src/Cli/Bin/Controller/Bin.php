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
namespace R3m\Io\Cli\Bin\Controller;

use R3m\Io\App;
use R3m\Io\Exception\ObjectException;
use R3m\Io\Module\Controller;
use R3m\Io\Module\Event;

use Exception;

use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;

class Bin extends Controller {
    const DIR = __DIR__;
    const NAME = 'Bin';

    const DEFAULT_NAME = 'r3m.io';
    const TARGET = '/usr/bin/';
    const EXE = 'R3m.php';

    const INFO = '{{binary()}} bin                            | Creates binary';

    /**
     * @throws ObjectException
     */
    public static function run(App $object){
        $name = $object->parameter($object, Bin::NAME, 1);
        if(empty($name)){
            $name = Bin::DEFAULT_NAME;
        }
        $url = false;
        $object->data('name', $name);
        try {
            $name = Bin::name('create', Bin::NAME);
            $url = Bin::locate($object, $name);
            $result = Bin::response($object, $url);
            Event::trigger($object, strtolower(Bin::NAME) . '.' . __FUNCTION__, [
                'name' => $name,
                'url' => $url
            ]);
            return $result;
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            Event::trigger($object, strtolower(Bin::NAME) . '.' . __FUNCTION__, [
                'name' => $name,
                'url' => $url,
                'exception' => $exception
            ]);
            return $exception;
        }
    }
}