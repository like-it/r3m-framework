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
use R3m\Io\Config;
use R3m\Io\Module\Dir;
use R3m\Io\Module\View;
use Exception;
use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;



class Autoload extends View{
    const NAME = 'Autoload';
    const DIR = __DIR__;

    const COMMAND_INFO = 'info';
    const COMMAND_RESTART = 'restart';
    const COMMAND = [
        Autoload::COMMAND_INFO,
        Autoload::COMMAND_RESTART
    ];

    const DEFAULT_COMMAND = Autoload::COMMAND_RESTART;

    const EXCEPTION_COMMAND_PARAMETER = '{$command}';
    const EXCEPTION_COMMAND = 'invalid command (' . Autoload::EXCEPTION_COMMAND_PARAMETER . ')' . PHP_EOL;

    public static function run($object){
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

    private static function info($object){
        try {
            $name = Autoload::name(__FUNCTION__, Autoload::NAME);
            $url = Autoload::locate($object, $name);
            return Autoload::response($object, $url);
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            return 'Command undefined.' . PHP_EOL;
        }
    }


    private static function restart($object){
        try {
            $name = Autoload::name(__FUNCTION__, Autoload::NAME);
            $url = Autoload::locate($object, $name);
            return Autoload::response($object, $url);
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            return 'Command undefined.' . PHP_EOL;
        }
    }
}