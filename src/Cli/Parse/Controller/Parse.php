<?php
/**
 * @author         Remco van der Velde
 * @since         2016-10-19
 * @version        1.0
 * @changeLog
 *     -    all
 */
namespace R3m\Io\Cli\Parse\Controller;

use Exception;
use R3m\Io\App;
use R3m\Io\Module\Dir;
use R3m\Io\Module\View;
use R3m\Io\Module\Parse as Parser;

class Parse extends View{
    const NAME = 'Parse';
    const DIR = __DIR__;

    const COMMAND_INFO = 'info';
    const COMMAND_RESTART = 'restart';
    const COMMAND = [
        Parse::COMMAND_INFO,
        Parse::COMMAND_RESTART
    ];

    const DEFAULT_COMMAND = Parse::COMMAND_INFO;

    const EXCEPTION_COMMAND_PARAMETER = '{$command}';
    const EXCEPTION_COMMAND = 'invalid command (' . Parse::EXCEPTION_COMMAND_PARAMETER . ')';

    public static function run($object){
        $command = $object->parameter($object, Parse::NAME, 1);

        if($command === null){
            $command = Parse::DEFAULT_COMMAND;
        }
        if(!in_array($command, Parse::COMMAND)){
            $exception = str_replace(
                Parse::EXCEPTION_COMMAND_PARAMETER,
                $command,
                Parse::EXCEPTION_COMMAND
            );
            throw new Exception($exception);
        }
        return Parse::{$command}($object);
    }

    private static function info($object){
        $url = Parse::locate($object, ucfirst(__FUNCTION__));
        return Parse::view($object, $url);
    }


    private static function restart($object){
        /*
        $parse = new Parser($object);
        $cache_dir = $parse->cache_dir();
        Dir::remove($cache_dir);
        */
        $url = Parse::locate($object, ucfirst(__FUNCTION__));
        return Parse::view($object, $url);
    }
}