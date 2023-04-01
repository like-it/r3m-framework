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
namespace R3m\Io\Cli\Route\Controller;

use R3m\Io\App;
use R3m\Io\Exception\ObjectException;
use R3m\Io\Module\Controller;

use Exception;

use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;
use R3m\Io\Module\Event;

class Route extends Controller {
    const NAME = 'Route';
    const DIR = __DIR__;

    const COMMAND_INFO = 'info';
    const COMMAND_RESTART = 'restart';
    const COMMAND = [
        Route::COMMAND_INFO,
        Route::COMMAND_RESTART
    ];

    const DEFAULT_COMMAND = Route::COMMAND_INFO;

    const EXCEPTION_COMMAND_PARAMETER = '{{$command}}';
    const EXCEPTION_COMMAND = 'invalid command (' . Route::EXCEPTION_COMMAND_PARAMETER . ')' . PHP_EOL;

    /**
     * @throws Exception
     */
    public static function run(App $object){
        $command = $object->parameter($object, Route::NAME, 1);
        if($command === null){
            $command = Route::DEFAULT_COMMAND;
        }
        if(!in_array($command, Route::COMMAND, true)){
            $exception = str_replace(
                Route::EXCEPTION_COMMAND_PARAMETER,
                $command,
                Route::EXCEPTION_COMMAND
            );
            $exception = new Exception($exception);
            Event::trigger($object, 'cli.' . strtolower(Route::NAME) . '.' . __FUNCTION__, [
                'command' => $command,
                'exception' => $exception,
            ]);
            throw $exception;
        }
        $response = Route::{$command}($object);
        Event::trigger($object, 'cli.' . strtolower(Route::NAME) . '.' . __FUNCTION__, [
            'command' => $command,
        ]);
        return $response;
    }

    private static function info(App $object){
        $name = false;
        $url = false;
        try {
            $name = Route::name(__FUNCTION__, Route::NAME);
            $url = Route::locate($object, $name);
            $response = Route::response($object, $url);
            Event::trigger($object, 'cli.' . strtolower(Route::NAME) . '.' . __FUNCTION__, [
                'name' => $name,
                'url' => $url,
            ]);
            return $response;
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            Event::trigger($object, 'cli.' . strtolower(Route::NAME) . '.' . __FUNCTION__, [
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
    private static function restart(App $object){
        $name = false;
        $url = false;
        try {
            $name = Route::name(__FUNCTION__    , Route::NAME);
            $url = Route::locate($object, $name);
            $response = Route::response($object, $url);
            Event::trigger($object, 'cli.' . strtolower(Route::NAME) . '.' . __FUNCTION__, [
                'name' => $name,
                'url' => $url,
            ]);
            return $response;
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            Event::trigger($object, 'cli.' . strtolower(Route::NAME) . '.' . __FUNCTION__, [
                'name' => $name,
                'url' => $url,
                'exception' => $exception
            ]);
            return $exception;
        }
    }
}