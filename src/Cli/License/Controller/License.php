<?php
/**
 * @author         Remco van der Velde
 * @since         2016-10-19
 * @version        1.0
 * @changeLog
 *     -    all
 */
namespace R3m\Io\Cli\License\Controller;

use Exception;
use R3m\Io\App;
use R3m\Io\Module\Core;
use R3m\Io\Module\Dir;
use R3m\Io\Module\View;
use R3m\Io\Module\Parse;

class License extends View{
    const NAME = 'License';
    const DIR = __DIR__;

    const COMMAND_INFO = 'info';
    const COMMAND = [
        License::COMMAND_INFO
    ];

    const DEFAULT_COMMAND = License::COMMAND_INFO;

    const EXCEPTION_COMMAND_PARAMETER = '{$command}';
    const EXCEPTION_COMMAND = 'invalid command (' . License::EXCEPTION_COMMAND_PARAMETER . ')';


    public static function run($object){
        $command = $object->parameter($object, License::NAME, 1);

        if($command === null){
            $command = License::DEFAULT_COMMAND;
        }
        if(!in_array($command, License::COMMAND)){
            $exception = str_replace(
                License::EXCEPTION_COMMAND_PARAMETER,
                $command,
                License::EXCEPTION_COMMAND
            );
            throw new Exception($exception);
        }
        return License::{$command}($object);
    }

    private static function info($object){
        $url = License::locate($object, ucfirst(__FUNCTION__));
        return License::view($object, $url);
    }
}
