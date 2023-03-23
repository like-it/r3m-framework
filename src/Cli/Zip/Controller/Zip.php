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
use R3m\Io\Exception\ObjectException;
use R3m\Io\Module\Controller;

use Exception;

use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;
use R3m\Io\Module\Event;

class Zip extends Controller {
    const DIR = __DIR__;
    const NAME = 'Zip';
    const INFO = [
        '{{binary()}} zip archive <source> <dest...> | Create a zip archive in <destination> from <source>',
        '{{binary()}} zip extract <source> <dest...> | Extract a zip archive in <destination> from <source>',
    ];
    const COMMAND = [
        'archive',
        'extract'
    ];

    /**
     * @throws ObjectException
     */
    public static function run(App $object){
        $command = false;
        $name = false;
        $url = false;
        try {
            $command = App::parameter($object, 'zip', 1);
            if($object->config('logger.default.name')){
                $object->logger($object->config('logger.default.name'))->info('Command: . ' . $command);
            }
            if(
                in_array(
                    $command,
                    Zip::COMMAND,
                    true
                )
            ) {
                $name = Zip::name($command, Zip::NAME);
                $url = Zip::locate($object, $name);
                $response = Zip::response($object, $url);
                Event::trigger($object, strtolower(Zip::NAME) . '.' . $command, [
                    'name' => $name,
                    'url' => $url,
                ]);
                return $response;
            }
            throw new Exception('Command undefined.' . PHP_EOL);
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            Event::trigger($object, strtolower(Zip::NAME) . '.' . __FUNCTION__, [
                'name' => $name,
                'url' => $url,
                'command' => $command,
                'exception' => $exception
            ]);
            return $exception;
        }
    }
}