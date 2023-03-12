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

class RamDisk extends Controller {
    const DIR = __DIR__;
    const NAME = 'RamDisk';
    const INFO = [
        '{{binary()}} ramdisk mount <size>           | RamDisk allocation',
        '{{binary()}} ramdisk unmount                | RamDisk allocation'
    ];

    public static function run(App $object){
        try {
            $name = RamDisk::name(__FUNCTION__    , RamDisk::NAME);
            $url = RamDisk::locate($object, $name);
            return RamDisk::response($object, $url);
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            return $exception;
        }
    }
}