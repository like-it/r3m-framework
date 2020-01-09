<?php
/**
 * @author         Remco van der Velde
 * @since         2016-10-19
 * @version        1.0
 * @changeLog
 *     -    all
 */
namespace R3m\Io\Cli\Autoload\Controller;

use Exception;
use R3m\Io\App;
use R3m\Io\Config;
use R3m\Io\Module\Dir;
use R3m\Io\Module\View;

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
    const EXCEPTION_COMMAND = 'invalid command (' . Autoload::EXCEPTION_COMMAND_PARAMETER . ')';

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
        $url = Autoload::locate($object, ucfirst(__FUNCTION__));
        return Autoload::view($object, $url);
    }


    private static function restart($object){
        $autoload = $object->data(App::AUTOLOAD_R3M);
        $cache_dir = $autoload->cache_dir();
        Dir::remove($cache_dir);
        $url = Autoload::locate($object, ucfirst(__FUNCTION__));
        return Autoload::view($object, $url);
    }
}