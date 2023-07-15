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
namespace R3m\Io\Cli\Cache\Controller;

use R3m\Io\App;
use R3m\Io\Config;
use R3m\Io\Module\Controller;
use R3m\Io\Module\Dir;
use R3m\Io\Module\Event;
use R3m\Io\Module\File;

use Exception;

use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;
use R3m\Io\Exception\ObjectException;

class Cache extends Controller {
    const NAME = 'Cache';
    const DIR = __DIR__;

    const COMMAND_INFO = 'info';
    const COMMAND_CLEAR = 'clear';
    const COMMAND_GARBAGE = 'garbage';
    const COMMAND_COLLECTOR = 'collector';
    const COMMAND = [
        Cache::COMMAND_INFO,
        Cache::COMMAND_CLEAR,
        Cache::COMMAND_GARBAGE
    ];

    const DEFAULT_COMMAND = Cache::COMMAND_INFO;

    const EXCEPTION_COMMAND_PARAMETER = '{{$command}}';
    const EXCEPTION_COMMAND = 'invalid command (' . Cache::EXCEPTION_COMMAND_PARAMETER . ')' . PHP_EOL;

    const CLEAR_COMMAND = [
        '{{binary()}} autoload restart',
        '{{binary()}} parse restart',
        '{{binary()}} route restart'
    ];

    const RAMDISK_CLEAR_COMMAND = '{{binary()}} ramdisk clear';

    const INFO = '{{binary()}} cache clear                    | Clears the app cache';

    /**
     * @throws Exception
     */
    public static function run(App $object){
        $command = $object->parameter($object, Cache::NAME, 1);
        if($command === null){
            $command = Cache::DEFAULT_COMMAND;
        }
        if(!in_array($command, Cache::COMMAND, true)){
            $exception = str_replace(
                Cache::EXCEPTION_COMMAND_PARAMETER,
                $command,
                Cache::EXCEPTION_COMMAND
            );
            $exception = new Exception($exception);
            Event::trigger($object, 'cli.' . strtolower(Cache::NAME) . '.' . __FUNCTION__, [
                'command' => $command,
                'exception' => $exception
            ]);
            throw $exception;
        }
        $response = Cache::{$command}($object);
        Event::trigger($object, 'cli.' . strtolower(Cache::NAME) . '.' . __FUNCTION__, [
            'command' => $command,
        ]);
        return $response;
    }

    /**
     * @throws ObjectException
     */
    private static function info(App $object){
        $name = false;
        $url = false;
        try {
            $name = Cache::name(__FUNCTION__, Cache::NAME);
            $url = Cache::locate($object, $name);
            $response = Cache::response($object, $url);
            Event::trigger($object, 'cli.' . strtolower(Cache::NAME) . '.' . __FUNCTION__, [
                'name' => $name,
                'url' => $url
            ]);
            return $response;
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            Event::trigger($object, 'cli.' . strtolower(Cache::NAME) . '.' . __FUNCTION__, [
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
    private static function clear(App $object){
        $name = false;
        $url = false;
        try {
            $object->config('ramdisk.is.disabled', true);
            $name = Cache::name(__FUNCTION__, Cache::NAME);
            $url = Cache::locate($object, $name);
            $response = Cache::response($object, $url);
            Event::trigger($object, 'cli.' . strtolower(Cache::NAME) . '.' . __FUNCTION__, [
                'name' => $name,
                'url' => $url
            ]);
            return $response;
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            Event::trigger($object, 'cli.' . strtolower(Cache::NAME) . '.' . __FUNCTION__, [
                'name' => $name,
                'url' => $url,
                'exception' => $exception
            ]);
            return $exception;
        }
    }

    private static function garbage(App $object){
        $name = false;
        $url = false;
        $command = $object->parameter($object, __FUNCTION__, 1);
        switch($command){
            case Cache::COMMAND_COLLECTOR :
                if($object->config('ramdisk.url')){
                    $dir = new Dir();

                    $dir_user_id = $dir->read($object->config('ramdisk.url'));

                    ddd($dir_user_id);

                    /*
                    $dir_cache = $object->config('ramdisk.url') .
                        $object->config(Config::POSIX_ID) .
                        $object->config('ds') .
                        'Cache' .
                        $object->config('ds')
                    ;
                    $read = $dir->read($dir_cache);
                    */
                    if(is_array($read)){
                        foreach($read as $file){
                            if($file->type === File::TYPE){
                                $file->mtime = File::mtime($file->url);
                                d($file);
                            }
                        }
                    }
                }
            break;
        }
        d($command);
        ddd('garbage');
        try {
            $object->config('ramdisk.is.disabled', true);
            $name = Cache::name(__FUNCTION__, Cache::NAME);
            $url = Cache::locate($object, $name);
            $response = Cache::response($object, $url);
            Event::trigger($object, 'cli.' . strtolower(Cache::NAME) . '.' . __FUNCTION__, [
                'name' => $name,
                'url' => $url
            ]);
            return $response;
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            Event::trigger($object, 'cli.' . strtolower(Cache::NAME) . '.' . __FUNCTION__, [
                'name' => $name,
                'url' => $url,
                'exception' => $exception
            ]);
            return $exception;
        }
    }
}
