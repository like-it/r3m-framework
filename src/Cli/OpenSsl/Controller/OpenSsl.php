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
namespace R3m\Io\Cli\OpenSsl\Controller;

use Exception;
use R3m\Io\App;
use R3m\Io\Module\Core;
use R3m\Io\Module\Dir;
use R3m\Io\Module\View;
use R3m\Io\Module\Parse;
use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;

class OpenSsl extends View{
    const NAME = 'OpenSsl';
    const DIR = __DIR__;

    const COMMAND_INFO = 'info';
    const COMMAND_REQ = 'req';
    const COMMAND = [
        OpenSsl::COMMAND_INFO,
        OpenSsl::COMMAND_REQ
    ];
    const DEFAULT_COMMAND = OpenSsl::COMMAND_INFO;

    const EXCEPTION_COMMAND_PARAMETER = '{$command}';
    const EXCEPTION_COMMAND = 'invalid command (' . OpenSsl::EXCEPTION_COMMAND_PARAMETER . ')' . PHP_EOL;

    const INFO = '{binary()} openssl                        | Open SSL Self-signed Certificate creation';

    public static function run($object){
        $command = $object->parameter($object, OpenSsl::NAME, 1);

        if($command === null){
            $command = OpenSsl::DEFAULT_COMMAND;
        }
        if(!in_array($command, OpenSsl::COMMAND)){
            $exception = str_replace(
                OpenSsl::EXCEPTION_COMMAND_PARAMETER,
                $command,
                OpenSsl::EXCEPTION_COMMAND
            );
            throw new Exception($exception);
        }
        return OpenSsl::{$command}($object);
    }

    private static function info($object)
    {
        try {
            $name = OpenSsl::name(__FUNCTION__, OpenSsl::NAME);
            $url = OpenSsl::locate($object, $name);
            return OpenSsl::response($object, $url);
        } catch (Exception | LocateException | UrlEmptyException | UrlNotExistException $exception) {
            return 'Command undefined.' . PHP_EOL;
        }
    }

    private static function req($object){
        try {
            $name = OpenSsl::name(__FUNCTION__, OpenSsl::NAME);
            $url = OpenSsl::locate($object, $name);
            return OpenSsl::response($object, $url);
        } catch (Exception | LocateException | UrlEmptyException | UrlNotExistException $exception) {
            return 'Command undefined.' . PHP_EOL;
        }
    }
}
