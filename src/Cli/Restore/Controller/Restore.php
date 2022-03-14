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
namespace R3m\Io\Cli\Restore\Controller;

use Exception;
use R3m\Io\App;
use R3m\Io\Config;
use R3m\Io\Module\File;
use R3m\Io\Module\Dir;
use R3m\Io\Module\View;
use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;

class Restore extends View {
    const DIR = __DIR__;
    const NAME = 'Restore';

    const DEFAULT_NAME = 'info';

    const INFO = '{{binary()}} restore                        | Restore system files';

    public static function run($object){
        $filename = $object->parameter($object, Restore::NAME, 1);
        if(empty($filename)){
            $filename = Restore::DEFAULT_NAME;
        }
        d($object->config('framework.dir'));
        dd($filename);
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