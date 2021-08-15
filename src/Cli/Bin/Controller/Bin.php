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

use Exception;
use R3m\Io\App;
use R3m\Io\Config;
use R3m\Io\Module\File;
use R3m\Io\Module\Dir;
use R3m\Io\Module\View;
use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;

class Bin extends View {
    const DIR = __DIR__;
    const NAME = 'Bin';

    const DEFAULT_NAME = 'r3m.io';
    const TARGET = '/usr/bin/';
    const EXE = 'R3m.php';

    const INFO = '{binary()} bin                            | Creates binary';

    public static function run($object){
        $name = $object->parameter($object, Bin::NAME, 1);
        if(empty($name)){
            $name = Bin::DEFAULT_NAME;
        }
        $object->data('name', $name);
        try {
            $name = Bin::name('create', Bin::NAME);
            $url = Bin::locate($object, $name);
            return Bin::response($object, $url);
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            return 'Command undefined.' . PHP_EOL;
        }
    }
}