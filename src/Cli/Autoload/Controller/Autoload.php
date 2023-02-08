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
namespace R3m\Io\Cli\Autoload\Controller;

use R3m\Io\App;
use R3m\Io\Module\Controller;

use Exception;

use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;

class Autoload extends Controller {
    const NAME = 'Autoload';
    const DIR = __DIR__;

    const COMMAND_INFO = 'info';
    const COMMAND_RESTART = 'restart';
    const COMMAND = [
        Autoload::COMMAND_INFO,
        Autoload::COMMAND_RESTART
    ];

    const DEFAULT_COMMAND = Autoload::COMMAND_RESTART;

    const EXCEPTION_COMMAND_PARAMETER = '{{$command}}';
    const EXCEPTION_COMMAND = 'invalid command (' . Autoload::EXCEPTION_COMMAND_PARAMETER . ')' . PHP_EOL;

    public static function run(App $object){
        $command = $object->parameter($object, Autoload::NAME, 1);

        if($command === null){
            $command = Autoload::DEFAULT_COMMAND;
        }
        if(!in_array($command, Autoload::COMMAND)){
            $exception = str_replace(
                Autoload::EXCEPTION_COMMAND_PARAMETER,
                $command,
                Autoload::EXCEPTION_COMMAND
            );
            throw new Exception($exception);
        }
        return Autoload::{$command}($object);
    }

    private static function info(App $object){
        try {
            $name = Autoload::name(__FUNCTION__, Autoload::NAME);
            $url = Autoload::locate($object, $name);
            return Autoload::response($object, $url);
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            return $exception;
        }
    }


    private static function restart(App $object){
        try {
            $name = Autoload::name(__FUNCTION__, Autoload::NAME);
            $url = Autoload::locate($object, $name);
            return Autoload::response($object, $url);
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            return $exception;
        }
    }
}