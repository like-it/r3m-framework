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

use R3m\Io\App;

use R3m\Io\Exception\ObjectException;
use R3m\Io\Module\Controller;
use R3m\Io\Module\Event;

use Exception;

use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;


class Openssl extends Controller {
    const NAME = 'OpenSsl';
    const DIR = __DIR__;

    const COMMAND_INFO = 'info';
    const COMMAND_REQ = 'req';
    const COMMAND = [
        Openssl::COMMAND_INFO,
        Openssl::COMMAND_REQ
    ];
    const DEFAULT_COMMAND = Openssl::COMMAND_INFO;

    const EXCEPTION_COMMAND_PARAMETER = '{{$command}}';
    const EXCEPTION_COMMAND = 'invalid command (' . Openssl::EXCEPTION_COMMAND_PARAMETER . ')' . PHP_EOL;

    const INFO = '{{binary()}} openssl                        | Open SSL Self-signed Certificate creation';

    /**
     * @throws Exception
     */
    public static function run(App $object){
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
            $exception = new Exception($exception);
            Event::trigger($object, strtolower(Openssl::NAME) . '.' . __FUNCTION__, [
                'command' => $command,
                'exception' => $exception
            ]);
            throw $exception;
        }
        $response = Openssl::{$command}($object);
        Event::trigger($object, strtolower(Openssl::NAME) . '.' . __FUNCTION__, [
            'command' => $command,
        ]);
        return $response;
    }

    /**
     * @throws ObjectException
     */
    private static function info(App $object)
    {
        $name = false;
        $url = false;
        try {
            $name = Openssl::name(__FUNCTION__, Openssl::NAME);
            $url = Openssl::locate($object, $name);
            $response = Openssl::response($object, $url);
            Event::trigger($object, 'openssl.info', [
                'name' => $name,
                'url' => $url
            ]);
            return $response;
        } catch (Exception | LocateException | UrlEmptyException | UrlNotExistException $exception) {
            Event::trigger($object, 'cli.' . strtolower(Openssl::NAME) . '.' . __FUNCTION__, [
                'name' => $name,
                'url' => $url,
                'exception' => $exception
            ]);
            return $exception;
        }
    }

    /**
     * @throws ObjectException
     */
    private static function req(App $object){
        $name = false;
        $url = false;
        try {
            $name = Openssl::name(__FUNCTION__, Openssl::NAME);
            $url = Openssl::locate($object, $name);
            $response = Openssl::response($object, $url);
            Event::trigger($object, 'cli.' . strtolower(Openssl::NAME) . '.' . __FUNCTION__, [
                'name' => $name,
                'url' => $url
            ]);
            return $response;
        } catch (Exception | LocateException | UrlEmptyException | UrlNotExistException $exception) {
            Event::trigger($object, 'cli.' . strtolower(Openssl::NAME) . '.' . __FUNCTION__, [
                'name' => $name,
                'url' => $url,
                'exception' => $exception
            ]);
            return $exception;
        }
    }
}