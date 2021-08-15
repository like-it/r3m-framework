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
namespace R3m\Io\Cli\Cache\Controller;

use Exception;
use R3m\Io\App;
use R3m\Io\Module\Core;
use R3m\Io\Module\Dir;
use R3m\Io\Module\View;
use R3m\Io\Module\Parse;
use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;

class Cache extends View{
    const NAME = 'Cache';
    const DIR = __DIR__;

    const COMMAND_INFO = 'info';
    const COMMAND_CLEAR = 'clear';
    const COMMAND = [
        Cache::COMMAND_INFO,
        Cache::COMMAND_CLEAR
    ];

    const DEFAULT_COMMAND = Cache::COMMAND_INFO;

    const EXCEPTION_COMMAND_PARAMETER = '{$command}';
    const EXCEPTION_COMMAND = 'invalid command (' . Cache::EXCEPTION_COMMAND_PARAMETER . ')' . PHP_EOL;

    const CLEAR_COMMAND = [
        '{binary()} autoload restart',
        '{binary()} parse restart',
        '{binary()} route restart'
    ];

    const INFO = '{binary()} cache clear                    | Clears the app cache';

    public static function run($object){
        $command = $object->parameter($object, Cache::NAME, 1);
        if($command === null){
            $command = Cache::DEFAULT_COMMAND;
        }
        if(!in_array($command, Cache::COMMAND)){
            $exception = str_replace(
                Cache::EXCEPTION_COMMAND_PARAMETER,
                $command,
                Cache::EXCEPTION_COMMAND
            );
            throw new Exception($exception);
        }
        return Cache::{$command}($object);
    }

    private static function info($object){
        try {
            $name = Cache::name(__FUNCTION__, Cache::NAME);
            $url = Cache::locate($object, $name);
            return Cache::response($object, $url);
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            return 'Command undefined.' . PHP_EOL;
        }

    }

    private static function clear($object){
        try {
            $name = Cache::name(__FUNCTION__, Cache::NAME);
            $url = Cache::locate($object, $name);
            return Cache::response($object, $url);
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            return 'Command undefined.' . PHP_EOL;
        }
    }
}
