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
use R3m\Io\Module\Controller;

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

    public static function run(App $object){
        try {
            $command = App::parameter($object, lcfirst(Ramdisk::NAME), 1);
            $name = false;
            switch (strtolower($command)){
                case 'mount':
                case 'unmount':
                case 'speedtest':
                    $name = RamDisk::name(strtolower($command), RamDisk::NAME);
                break;
                default:
                    throw new Exception('Unknown ramdisk command...');
            }
            if($name){
                $url = RamDisk::locate($object, $name);
                return RamDisk::response($object, $url);
            }
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            return $exception;
        }
    }
}