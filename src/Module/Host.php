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
        $object->data($key, $value);
        $key = 'host.scheme';
        $value = Host::scheme();
        $object->data($key, $value);
        $key = 'host.extension';
        $value = Host::extension();
        $object->data($key, $value);
        $key = 'host.domain';
        $value = Host::domain();
        $object->data($key, $value);
        $key = 'host.subdomain';
        $subdomain = Host::subdomain();
        $object->data($key, $subdomain);
        $key = 'host.port';
        $port = Host::port();
        $object->data($key, $port);

        $config = $object->data(App::NAMESPACE . '.' . Config::NAME);
        $key = 'host.dir.root';
        if(empty($subdomain)){
            $sentence = Core::ucfirst_sentence(
                $object->data('host.domain') .
                $config->data('ds') .
                $object->data('host.extension') .
                $config->data('ds'),
                $config->data('ds')
            );
            $sentence = ltrim($sentence, $object->config('ds'));
            $value =
                $config->data('project.dir.root') .
                $config->data(Config::DICTIONARY . '.' . Config::HOST) .
                $config->data('ds') .
                $sentence;
        } else {
            $sentence = Core::ucfirst_sentence(
                $object->data('host.subdomain') .
                $config->data('ds') .
                $object->data('host.domain') .
                $config->data('ds') .
                $object->data('host.extension') .
                $config->data('ds'),
                $config->data('ds')
            );
            $sentence = ltrim($sentence, $object->config('ds'));
            $value =
                $config->data('project.dir.root') .
                $config->data(Config::DICTIONARY . '.' . Config::HOST) .
                $config->data('ds') .
                $sentence;
        }
        $config->data($key, $value);
        $key = 'host.dir.data';
        $value =
            $config->data('host.dir.root') .
            $config->data(Config::DICTIONARY . '.' . Config::DATA) .
            $config->data('ds');
        $config->data($key, $value);
        $key = 'host.dir.cache';
        $value =
            Dir::name($config->data('framework.dir.cache'), 2) .
            $config->data(Config::DICTIONARY . '.' . Config::HOST) .
            $config->data('ds');
        $config->data($key, $value);
        $key = 'host.dir.public';
        $value =
            $config->data('host.dir.root') .
            $config->data(Config::DICTIONARY . '.' . Config::PUBLIC) .
            $config->data('ds');
        $config->data($key, $value);
        $key = 'host.dir.source';
        $value =
            $config->data('host.dir.root') .
            $config->data(Config::DICTIONARY . '.' . Config::SOURCE) .
            $config->data('ds');
        $config->data($key, $value);
        $key = 'host.dir.view';
        $value =
            $config->data('host.dir.root') .
            $config->data(Config::DICTIONARY . '.' . Config::VIEW) .
            $config->data('ds');
        $config->data($key, $value);
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