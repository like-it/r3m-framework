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

    /**
     * @throws ObjectException
     * @throws Exception
     */
    private static function garbage(App $object){
        $command = $object->parameter($object, __FUNCTION__, 1);
        $options = App::options($object);
        $flags = App::flags($object);
        if(!property_exists($options, 'minute')){
            $options->minute = 15;
        }
        switch($command){
            case Cache::COMMAND_COLLECTOR :
                if($object->config('ramdisk.url')){
                    $dir = new Dir();
                    $dir_user_id = $dir->read($object->config('ramdisk.url'));
                    if(is_array($dir_user_id)){
                        foreach($dir_user_id as $file){
                            $dir_cache = $file->url .
                                'Cache' .
                                $object->config('ds')
                            ;
                            $read = $dir->read($dir_cache);
                            $size_freed = 0;
                            $counter = 0;
                            if(is_array($read)){
                                foreach($read as $file){
                                    if($file->type === File::TYPE){
                                        $file->mtime = File::mtime($file->url);
                                        if($file->mtime < time() - ($options->minute * 60)){
                                            $size_freed += File::size($file->url);
                                            File::delete($file->url);
                                            $counter++;
                                        }
                                    }
                                }
                            }
                            echo 'Garbage Collector: amount freed: ' . $counter . ' size: ' . $size_freed . ' bytes' . PHP_EOL;
                            if($object->config('project.log.name')){
                                $object->logger($object->config('project.log.name'))->info('Garbage Collector: amount freed: ' . $counter . ' size: ' . $size_freed . ' bytes' . PHP_EOL, [ $dir_cache ]);
                            }
                            Event::trigger($object, 'cli.' . strtolower(Cache::NAME) . '.' . __FUNCTION__, [
                                'command' => $command,
                                'url' => $dir_cache,
                                'options' => $options,
                                'flags' => $flags,
                                'amount' => $counter,
                                'size' => $size_freed
                            ]);
                        }
                    }
                }
            break;
        }
    }
}
