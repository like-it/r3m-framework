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
namespace R3m\Io\Cli\Doctrine\Controller;

use R3m\Io\App;
use R3m\Io\Exception\ObjectException;
use R3m\Io\Module\Controller;

use Exception;

use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;
use R3m\Io\Module\Event;

class Doctrine extends Controller {
    const DIR = __DIR__;
    const NAME = 'Doctrine';
    const INFO = '{{binary()}} doctrine orm:generate-proxies  | Generate proxies & adjust owner';
    const INFO_RUN = [
        '{{binary()}} doctrine orm:generate-proxies  | Generate proxies & adjust owner'
    ];

    /**
     * @throws ObjectException
     */
    public static function run(App $object){
        $command = false;
        $name = false;
        $url = false;
        try {
            $command = App::parameter($object, 'doctrine', 1);
            $name = Doctrine::name($command, Doctrine::NAME);
            $url = Doctrine::locate($object, $name);
            $response = Doctrine::response($object, $url);
            Event::trigger($object, strtolower(Doctrine::NAME) . '.' . __FUNCTION__, [
                'command' => $command,
                'name' => $name,
                'url' => $url
            ]);
            return $response;
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            Event::trigger($object, strtolower(Doctrine::NAME) . '.' . __FUNCTION__, [
                'command' => $command,
                'name' => $name,
                'url' => $url,
                'exception' => $exception
            ]);
            return $exception;
        }
    }
}