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
namespace R3m\Io\Cli\License\Controller;

use Exception;
use R3m\Io\App;
use R3m\Io\Module\Core;
use R3m\Io\Module\Dir;
use R3m\Io\Module\View;
use R3m\Io\Module\Parse;
use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;

class License extends View{
    const NAME = 'License';
    const DIR = __DIR__;

    const COMMAND_INFO = 'info';
    const COMMAND = [
        License::COMMAND_INFO
    ];
    const DEFAULT_COMMAND = License::COMMAND_INFO;

    const EXCEPTION_COMMAND_PARAMETER = '{$command}';
    const EXCEPTION_COMMAND = 'invalid command (' . License::EXCEPTION_COMMAND_PARAMETER . ')' . PHP_EOL;

    const INFO = '{binary()} license                        | R3m/framework license';

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

    private static function info($object)
    {
        try {
            $name = License::name(__FUNCTION__, License::NAME);
            $url = License::locate($object, $name);
            return License::response($object, $url);
        } catch (Exception | LocateException | UrlEmptyException | UrlNotExistException $exception) {
            return 'Command undefined.' . PHP_EOL;
        }
    }
}
