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
namespace R3m\Io\Cli\Zip\Controller;

use R3m\Io\App;
use R3m\Io\Module\Logger;
use R3m\Io\Module\View;
use Exception;
use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;

class Zip extends View {
    const DIR = __DIR__;
    const NAME = 'Zip';
    const INFO = [
        '{{binary()}} zip archive <source> <dest...> | it creates a zip archive in <destination> from <source>',
        '{{binary()}} zip extract <source> <dest...> | it extracts a zip archive in <destination> from <source>',
    ];
    
    public static function run(App $object){
        Logger::error('zip');
        $command = App::parameter($object, 'zip', 1);
        try {
            $name = Zip::name(__FUNCTION__, Zip::NAME);
            $url = Zip::locate($object, $name);
            return Zip::response($object, $url);
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            return 'Command undefined.' . PHP_EOL;;
        }
    }
}