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
namespace R3m\Io\Cli\Parse\Controller;

use Exception;
use R3m\Io\App;
use R3m\Io\Module\Core;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;
use R3m\Io\Module\View;
use R3m\Io\Module\Parse as Parser;
use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;

class Parse extends View{
    const NAME = 'Parse';
    const DIR = __DIR__;

    const COMMAND_INFO = 'info';
    const COMMAND_RESTART = 'restart';
    const COMMAND_COMPILE = 'compile';
    const COMMAND = [
        Parse::COMMAND_INFO,
        Parse::COMMAND_RESTART,
        Parse::COMMAND_COMPILE
    ];

    const DEFAULT_COMMAND = Parse::COMMAND_INFO;

    const EXCEPTION_COMMAND_PARAMETER = '{{$command}}';
    const EXCEPTION_COMMAND = 'invalid command (' . Parse::EXCEPTION_COMMAND_PARAMETER . ')' . PHP_EOL;

    /**
     * @throws Exception
     */
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
        try {
            $name = Parse::name(__FUNCTION__, Parse::NAME);
            $url = Parse::locate($object, $name);
            return Parse::response($object, $url);
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            return 'Command undefined.' . PHP_EOL;
        }
    }

    private static function restart($object){
        try {
            $name = Parse::name(__FUNCTION__, Parse::NAME);
            $url = Parse::locate($object, $name);
            return Parse::response($object, $url);
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            return 'Command undefined.' . PHP_EOL;
        }
    }

    /**
     * @throws Exception
     */
    private static function compile($object){
        $url = $object->parameter($object, __FUNCTION__, 1);
        if(File::exist($url)){
            $read = File::read($url);
            if($read){
                $mtime = File::mtime($url);
                $parse = new \R3m\Io\Module\Parse($object);
                $parse->storage()->data('r3m.io.parse.view.url', $url);
                $parse->storage()->data('r3m.io.parse.view.mtime', $mtime);
                $object->data('ldelim', '{');
                $object->data('rdelim', '}');
                $data = clone $object->data();
                unset($data->{App::NAMESPACE});
                $read = $parse->compile($read, $data, $parse->storage());
                return $read;
        }
    }
}