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
namespace R3m\Io\Cli\RamDisk\Controller;

use R3m\Io\App;

use R3m\Io\Exception\ObjectException;
use R3m\Io\Module\Controller;
use R3m\Io\Module\Event;

use Exception;

use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;

class Ramdisk extends Controller {
    const DIR = __DIR__;
    const NAME = 'Ramdisk';
    const INFO = [
        '{{binary()}} ramdisk clear                  | RamDisk clear',
        '{{binary()}} ramdisk mount <size>           | RamDisk allocation',
        '{{binary()}} ramdisk speedtest              | RamDisk speedtest',
        '{{binary()}} ramdisk unmount                | RamDisk unmount'
    ];

    /**
     * @throws ObjectException
     */
    public static function run(App $object){
        $name = false;
        $url = false;
        $command = false;
        try {
            $command = App::parameter($object, lcfirst(Ramdisk::NAME), 1);
            $name = false;
            switch (strtolower($command)){
                case 'mount':
                case 'unmount':
                case 'speedtest':
                case 'clear':
                    $name = RamDisk::name(strtolower($command), RamDisk::NAME);
                break;
                default:
                    $exception = new Exception('Unknown ramdisk command...');
                    Event::trigger($object, strtolower(Ramdisk::NAME) . '.' . __FUNCTION__, [
                        'command' => $command,
                        'exception' => $exception
                    ]);
                    throw $exception;
            }
            if($name){
                $url = RamDisk::locate($object, $name);
                $response = RamDisk::response($object, $url);
                Event::trigger($object, strtolower(Ramdisk::NAME) . '.' . strtolower($command), [
                    'name' => $name,
                    'url' => $url
                ]);
                return $response;
            }
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            Event::trigger($object, strtolower(Ramdisk::NAME) . '.' . __FUNCTION__, [
                'name' => $name,
                'url' => $url,
                'command' => $command,
                'exception' => $exception
            ]);
            return $exception;
        }
    }
}