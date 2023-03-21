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
namespace R3m\Io\Cli\Cache\Clear\Controller\Cache;

use R3m\Io\App;
use R3m\Io\Module\Controller;
use R3m\Io\Module\Dir;

use Exception;

use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;

class Clear extends Controller {
    const NAME = 'Cache';
    const DIR = __DIR__;

    const COMMAND_INFO = 'info';
    const COMMAND_CLEAR = 'clear';
    const COMMAND = [
        Clear::COMMAND_INFO,
        Clear::COMMAND_CLEAR
    ];

    const DEFAULT_COMMAND = Clear::COMMAND_INFO;

    const EXCEPTION_COMMAND_PARAMETER = '{{$command}}';
    const EXCEPTION_COMMAND = 'invalid command (' . Clear::EXCEPTION_COMMAND_PARAMETER . ')' . PHP_EOL;

    const CLEAR_COMMAND = [
        '{{binary()}} autoload restart',
        '{{binary()}} parse restart',
        '{{binary()}} route restart'
    ];

    const INFO = '{{binary()}} cache:clear                    | Clears the app cache';

    /**
     * @throws Exception
     */
    public static function run(App $object){
        $command = Clear::COMMAND_CLEAR;
        if(!in_array($command, Clear::COMMAND)){
            $exception = str_replace(
                Clear::EXCEPTION_COMMAND_PARAMETER,
                $command,
                Clear::EXCEPTION_COMMAND
            );
            throw new Exception($exception);
        }
        return Clear::{$command}($object);
    }

    private static function info(App $object){
        try {
            $name = Clear::name(__FUNCTION__, Clear::NAME);
            $url = Clear::locate($object, $name);
            return Clear::response($object, $url);
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            return $exception;
        }

    }

    private static function clear(App $object){
        try {
            $object->config('ramdisk.is.disabled', true);
            $name = Clear::name(__FUNCTION__, Clear::NAME);
            $url = Clear::locate($object, $name);
            $response = Clear::response($object, $url);
            return $response;


        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            return $exception;
        }
    }
}
