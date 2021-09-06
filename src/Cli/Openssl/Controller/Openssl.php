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
namespace R3m\Io\Cli\Openssl\Controller;

use Exception;
use R3m\Io\App;
use R3m\Io\Module\Core;
use R3m\Io\Module\Dir;
use R3m\Io\Module\View;
use R3m\Io\Module\Parse;
use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;

class Openssl extends View{
    const NAME = 'OpenSsl';
    const DIR = __DIR__;

    const COMMAND_INFO = 'info';
    const COMMAND_REQ = 'req';
    const COMMAND = [
        Openssl::COMMAND_INFO,
        Openssl::COMMAND_REQ
    ];
    const DEFAULT_COMMAND = Openssl::COMMAND_INFO;

    const EXCEPTION_COMMAND_PARAMETER = '{$command}';
    const EXCEPTION_COMMAND = 'invalid command (' . Openssl::EXCEPTION_COMMAND_PARAMETER . ')' . PHP_EOL;

    const INFO = '{binary()} openssl                        | Open SSL Self-signed Certificate creation';

    public static function run($object){
        $command = $object->parameter($object, Openssl::NAME, 1);

        if($command === null){
            $command = Openssl::DEFAULT_COMMAND;
        }
        if(!in_array($command, Openssl::COMMAND)){
            $exception = str_replace(
                Openssl::EXCEPTION_COMMAND_PARAMETER,
                $command,
                Openssl::EXCEPTION_COMMAND
            );
            throw new Exception($exception);
        }
        return Openssl::{$command}($object);
    }

    private static function info($object)
    {
        try {
            $name = Openssl::name(__FUNCTION__, Openssl::NAME);
            $url = Openssl::locate($object, $name);
            return Openssl::response($object, $url);
        } catch (Exception | LocateException | UrlEmptyException | UrlNotExistException $exception) {
            return 'Command undefined.' . PHP_EOL;
        }
    }

    private static function req($object){
        try {
            $name = Openssl::name(__FUNCTION__, Openssl::NAME);
            $url = Openssl::locate($object, $name);
            return Openssl::response($object, $url);
        } catch (Exception | LocateException | UrlEmptyException | UrlNotExistException $exception) {
            return 'Command undefined.' . PHP_EOL;
        }
    }
}
