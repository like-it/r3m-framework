<?php
/**
 * @author         Remco van der Velde
 * @since         2016-10-19
 * @version        1.0
 * @changeLog
 *     -    all
 */
namespace R3m\Io\Cli;

use Exception;
use R3m\Io\App;
use R3m\Io\Module\Dir;
use R3m\Io\Module\View;

class Autoload extends View{
    const NAME = 'Autoload';
    const DIR = __DIR__;

    const COMMAND_RESTART = 'restart';
    const COMMAND = [
        Autoload::COMMAND_RESTART
    ];

    const EXCEPTION_COMMAND_PARAMETER = '{$command}';
    const EXCEPTION_COMMAND = 'invalid command (' . Autoload::EXCEPTION_COMMAND_PARAMETER . ')';

    public static function run($object){
        $command = $object->parameter($object, Autoload::NAME, 1);
        if(!in_array($command, Autoload::COMMAND)){
            $exception = str_replace(
                Autoload::EXCEPTION_COMMAND_PARAMETER,
                $command,
                Autoload::EXCEPTION_COMMAND
            );
            throw new Exception($exception);
        }
        switch($command){
            case Autoload::COMMAND_RESTART :
                return Autoload::{$command}($object);
            break;
        }
    }

    private static function restart($object){
        $config = $object->data(App::DATA_CONFIG);
        $dir_root = $config->data('framework.dir.root');
        $temp  = $dir_root . 'Temp' . $config->data('ds');
        Dir::remove($temp);
        $url = Autoload::locate($object, Autoload::NAME . '/' . 'Restart');
        return Autoload::view($object, $url);
    }
}