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

use stdClass;
use R3m\Io\App;
use R3m\Io\Config;
use R3m\Io\Module\Core;

class Host {
    const SCHEME_HTTP = 'http';
    const SCHEME_HTTPS = 'https';

    public static function configure(App $object){
        if(defined('IS_CLI')){
            return $object;
        }
        $key = 'host.url';
        $value = Host::url();
        $object->config($key, $value);
        $key = 'host.scheme';
        $value = Host::scheme();
        $object->config($key, $value);
        $key = 'host.extension';
        $value = Host::extension();
        $object->config($key, $value);
        $key = 'host.domain';
        $value = Host::domain();
        $object->config($key, $value);
        $key = 'host.subdomain';
        $subdomain = Host::subdomain();
        $object->config($key, $subdomain);
        $key = 'host.port';
        $port = Host::port();
        $object->config($key, $port);

//        $config = $object->data(App::NAMESPACE . '.' . Config::NAME);
        $key = 'host.dir.root';
        if(empty($subdomain)){
            $sentence = Core::ucfirst_sentence(
                $object->config('host.domain') .
                $object->config('ds') .
                $object->config('host.extension') .
                $object->config('ds'),
                $object->config('ds')
            );
            $sentence = ltrim($sentence, $object->config('ds'));
            $value =
                $object->config('project.dir.root') .
                $object->config(Config::DICTIONARY . '.' . Config::HOST) .
                $object->config('ds') .
                $sentence;
        } else {
            $sentence = Core::ucfirst_sentence(
                $object->config('host.subdomain') .
                $object->config('ds') .
                $object->config('host.domain') .
                $object->config('ds') .
                $object->config('host.extension') .
                $object->config('ds'),
                $object->config('ds')
            );
            $sentence = ltrim($sentence, $object->config('ds'));
            $value =
                $object->config('project.dir.root') .
                $object->config(Config::DICTIONARY . '.' . Config::HOST) .
                $object->config('ds') .
                $sentence;
        }
        $object->config($key, $value);
        $key = 'host.dir.data';
        $value =
            $object->config('host.dir.root') .
            $object->config(Config::DICTIONARY . '.' . Config::DATA) .
            $object->config('ds');
        $object->config($key, $value);
        $key = 'host.dir.cache';
        $value =
            Dir::name($object->config('framework.dir.cache'), 2) .
            $object->config(Config::DICTIONARY . '.' . Config::HOST) .
            $object->config('ds');
        $object->config($key, $value);
        $key = 'host.dir.public';
        $value =
            $object->config('host.dir.root') .
            $object->config(Config::DICTIONARY . '.' . Config::PUBLIC) .
            $object->config('ds');
        $object->config($key, $value);
        $key = 'host.dir.source';
        $value =
            $object->config('host.dir.root') .
            $object->config(Config::DICTIONARY . '.' . Config::SOURCE) .
            $object->config('ds');
        $object->config($key, $value);
        $key = 'host.dir.view';
        $value =
            $object->config('host.dir.root') .
            $object->config(Config::DICTIONARY . '.' . Config::VIEW) .
            $object->config('ds');
        $object->config($key, $value);
        return $object;
    }

    public static function url($include_scheme = true){
        if(isset($_SERVER['HTTP_HOST'])){
            $domain = $_SERVER['HTTP_HOST'];
        }
        elseif(isset($_SERVER['SERVER_NAME'])){
            $domain = $_SERVER['SERVER_NAME'];
        } else {
            $domain = '';
        }
        if($include_scheme) {
            $scheme = Host::scheme();
            $host = '';
            if(isset($scheme) && isset($domain)){
                $host = $scheme . '://' . $domain . '/';
            }
        } else {
            $host = $domain;
        }
        return $host;
    }

    public static function domain($host=''){
        if(empty($host)){
            if(isset($_SERVER['HTTP_HOST'])){
                $host = $_SERVER['HTTP_HOST'];
            }
        }
        if(empty($host)){
            return false;
        }
        $explode = explode('.', $host);
        if(count($explode) >= 2){
            array_pop($explode);
            return array_pop($explode);
        }
        return false;
    }

    public static function subdomain($host=''){
        if(empty($host)){
            if(isset($_SERVER['HTTP_HOST'])){
                $host = $_SERVER['HTTP_HOST'];
            }
        }
        if(empty($host)){
            return false;
        }
        $explode = explode('.', $host);
        if(count($explode) > 2){
            array_pop($explode);
            array_pop($explode);
            return implode('.', $explode);
        }
        return false;
    }

    public static function port($host=''){
        if(empty($host)){
            if(isset($_SERVER['HTTP_HOST'])){
                $host = $_SERVER['HTTP_HOST'];
            }
        }
        if(empty($host)){
            return false;
        }
        $explode = explode(':', $host);
        if(count($explode) >= 2){
            $string = array_pop($explode);
            $test = explode('?', $string);
            return $test[0];
        } elseif(isset($_SERVER['SERVER_PORT'])) {
            return $_SERVER['SERVER_PORT'];
        }
        return false;
    }

    public static function extension($host=''){
        if(empty($host)){
            if(isset($_SERVER['HTTP_HOST'])){
                $host = $_SERVER['HTTP_HOST'];
            }
        }
        if(empty($host)){
            return false;
        }
        $host = explode(':', $host, 2);
        if(array_key_exists(1, $host)){
            array_pop($host);
        }
        $host = implode(':', $host);
        $explode = explode('.', $host);
        if(count($explode) > 1){
            return array_pop($explode);
        }
        return false;
    }

    public static function remove_port($url=''){
        $explode = explode(':', $url, 3);
        if(isset($explode[2])){
            array_pop($explode);
            return implode(':', $explode);
        }
        return '';
    }

    public static function remove_scheme($url=''){
        $explode = explode('://', $url, 2);
        if(isset($explode[1])){
            if(substr($explode[1], -1, 1) == '/'){
                return substr($explode[1], 0, -1);
            }
            return $explode[1];
        }
        return '';
    }

    public static function scheme(){
        $scheme = Host::SCHEME_HTTP;
        if(!empty($_SERVER['REQUEST_SCHEME'])){
            $scheme = $_SERVER['REQUEST_SCHEME'];
        } else {
            if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'){
                $scheme = Host::SCHEME_HTTPS;
            }
        }
        return $scheme;
    }

    public static function isIp4Address(){
        $subdomain = Host::subdomain();
        $domain = Host::domain();
        $extension = Host::extension();
        $explode = explode('.', $subdomain);
        foreach($explode as $possibility){
            if(!intval($possibility) > 0){
                return false;
            }
        }
        if(!intval($domain) > 0){
            return false;
        }
        if(!intval($extension) > 0){
            return false;
        }
        return true;
    }
}