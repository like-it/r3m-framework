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
use R3m\Io\Module\View;
use Exception;
use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;

class Doctrine extends View {
    const DIR = __DIR__;
    const NAME = 'Doctrine';
    const INFO = '{binary()} doctrine orm:generate-proxies  | Generate proxies & adjust owner';
    const INFO_RUN = [
        '{binary()} doctrine orm:generate-proxies  | Generate proxies & adjust owner'
    ];
    
    public static function run($object){
        try {
            $command = App::parameter($object, 'doctrine', 1);
            $name = Doctrine::name($command, Doctrine::NAME);
            $url = Doctrine::locate($object, $name);
            return Doctrine::response($object, $url);
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            return 'Command undefined.' . PHP_EOL;;
        }
    }
}