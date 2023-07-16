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
namespace R3m\Io\Module;

use R3m\Io\App;
use R3m\Io\Config;

use Exception;

use R3m\Io\Exception\DirectoryCreateException;
use R3m\Io\Exception\FileWriteException;
use R3m\Io\Exception\ObjectException;

class Cache {

    /**
     * @throws ObjectException
     * @throws Exception
     */
    public static function key(App $object, $options=[]): ?string
    {
        if(!array_key_exists('url', $options)){
            return null;
        }
        if(!array_key_exists('ttl', $options)){
            $options['ttl'] = $object->config('cache.controller.ttl') ?? 600;
        }
        if(is_numeric($options['ttl'] )){
            $options['ttl']  += 0;
        } else {
            $options['ttl']  = 'INF';   // will be removed with cache:clear command
        }
        $key = [
            'url' => $options['url']
        ];
        if(
            array_key_exists('object', $options) &&
            $options['object'] === true
        ){
            //per user cache
            $key['object'] = $object->data();
        }
        if(
            array_key_exists('request', $options) &&
            $options['request'] === true
        ){
            //per request cache
            $key['request'] = $object->request();
        }
        if(
            array_key_exists('route', $options) &&
            $options['route'] === true
        ){
            //per route cache
            $key['route'] = $object->route()->current();
        }
        if($object->session('has')){
            //add session
            $key['session'] = $object->session();
        }
        elseif(
            array_key_exists('session', $options) &&
            $options['session'] === true
        ){
            //add session
            $key['session'] = $object->session();
        }
        return $options['ttl'] .
            $object->config('ds') .
            sha1(Core::object($key, Core::OBJECT_JSON_LINE)) .
            '.' .
            File::basename($options['url'])
        ;
    }

    public static function read(App $object, $options=[]){
        if(!array_key_exists('key', $options)){
            return null;
        }
        if(!array_key_exists('ttl', $options)){
            $options['ttl'] = $object->config('cache.controller.ttl') ?? 600;
        }
        if(is_numeric($options['ttl'] )){
            $options['ttl']  += 0;
        } else {
            $options['ttl']  = 'INF';   // will be removed with cache:clear command
        }
        if($object->config('ramdisk.url')){
            $dir_cache =
                $object->config('ramdisk.url') .
                $object->config(Config::POSIX_ID) .
                $object->config('ds') .
                'Cache' .
                $object->config('ds')
            ;
            $url_cache = $dir_cache . $options['key'] . $object->config('extension.response');
            if(File::exist($url_cache)){
                if(is_numeric($options['ttl'])){
                    $mtime = File::mtime($url_cache);
                    if($mtime + $options['ttl'] > time()){
                        return File::read($url_cache);
                    }
                } else {
                    return File::read($url_cache);
                }
            }
        }
        return null;
    }

    /**
     * @throws DirectoryCreateException
     * @throws FileWriteException
     */
    public static function write(App $object, $options=[]): ?int
    {
        if(!array_key_exists('key', $options)){
            return null;
        }
        if(!array_key_exists('data', $options)){
            return null;
        }
        if($object->config('ramdisk.url')){
            $dir_cache =
                $object->config('ramdisk.url') .
                $object->config(Config::POSIX_ID) .
                $object->config('ds') .
                'Cache' .
                $object->config('ds')
            ;
            $url_cache = $dir_cache . $options['key'] . $object->config('extension.response');
            $dir_duration = Dir::name($url_cache);
            Dir::create($dir_duration, Dir::CHMOD);
            return File::write($url_cache, $options['data']);
        }
        return null;
    }

    public static function delete(App $object, $options=[]): bool
    {
        if(!array_key_exists('key', $options)){
            return false;
        }
        if($object->config('ramdisk.url')){
            $dir_cache =
                $object->config('ramdisk.url') .
                $object->config(Config::POSIX_ID) .
                $object->config('ds') .
                'Cache' .
                $object->config('ds')
            ;
            $url_cache = $dir_cache . $options['key'] . $object->config('extension.response');
            return File::delete($url_cache);
        }
        return false;
    }
}