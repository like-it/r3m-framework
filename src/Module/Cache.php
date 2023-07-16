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

    const ROUTE = 'route';
    const REQUEST = 'request';
    const SESSION = 'session';
    const OBJECT = 'object';
    const TTL = 'ttl';
    const URL = 'url';

    const ONE_MINUTE = 60;
    const TWO_MINUTES = 120;
    const THREE_MINUTES = 180;
    const FOUR_MINUTES = 240;
    const FIVE_MINUTES = 300;
    const SIX_MINUTES = 360;
    const SEVEN_MINUTES = 420;
    const EIGHT_MINUTES = 480;
    const NINE_MINUTES = 540;
    const TEN_MINUTES = 600;
    const FIFTEEN_MINUTES = 900;
    const TWENTY_MINUTES = 1200;
    const TWENTY_FIVE_MINUTES = 1500;
    const THIRTY_MINUTES = 1800;
    const THIRTY_FIVE_MINUTES = 1800;
    const FORTY_MINUTES = 2400;
    const FORTY_FIVE_MINUTES = 2700;
    const FIFTY_MINUTES = 3000;
    const FIFTY_FIVE_MINUTES = 3300;
    const SIXTY_MINUTES = 3000;

    const ONE_HOUR = 3600;
    const TWO_HOURS = 7200;
    const THREE_HOURS = 10800;
    const FOUR_HOURS = 14400;
    const FIVE_HOURS = 18000;
    const SIX_HOURS = 21600;
    const SEVEN_HOURS = 25200;
    const EIGHT_HOURS = 28800;
    const NINE_HOURS = 32400;
    const TEN_HOURS = 36000;
    const ELEVEN_HOURS = 39600;
    const TWELVE_HOURS = 43200;
    const THIRTEEN_HOURS = 46800;
    const FOURTEEN_HOURS = 50400;
    const FIFTEEN_HOURS = 54000;
    const SIXTEEN_HOURS = 57600;
    const SEVENTEEN_HOURS = 61200;
    const EIGHTEEN_HOURS = 64800;
    const NINETEEN_HOURS = 68400;
    const TWENTY_HOURS = 72000;
    const TWENTY_ONE_HOURS = 75600;
    const TWENTY_TWO_HOURS = 79200;
    const TWENTY_THREE_HOURS = 82800;
    const TWENTY_FOUR_HOURS = 86400;

    const ONE_DAY = 86400;
    const TWO_DAYS = 172800;
    const THREE_DAYS = 259200;
    const FOUR_DAYS = 345600;
    const FIVE_DAYS = 432000;
    const SIX_DAYS = 518400;
    const SEVEN_DAYS = 604800;
    const ONE_WEEK = 604800;
    const TWO_WEEKS = 1209600;
    const THREE_WEEKS = 1814400;
    const FOUR_WEEKS = 2419200;
    const ONE_MONTH = 2592000;
    const TWO_MONTHS = 5184000;
    const THREE_MONTHS = 7776000;
    const ONE_QUARTER = 7776000;
    const TWO_QUARTERS = 15552000;
    const THREE_QUARTERS = 23328000;
    const FOUR_QUARTERS = 31536000;
    const ONE_YEAR = 31536000;
    const TWO_YEARS = 63072000;
    const THREE_YEARS = 94608000;
    const FOUR_YEARS = 126144000;
    const FIVE_YEARS = 157680000;

    const INF = 'INF';

    public static function url($object, $type=Cache::REQUEST){
        switch($type){
            case Cache::ROUTE:
                $class_call = constant(get_called_class() . '::class');
                ddd($class_call);
                $url = $object->route()->current();
        }

    }

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