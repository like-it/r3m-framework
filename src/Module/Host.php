<?php
/**
 *  (c) 2019 Priya.software
 *
 *  License: MIT
 *
 *  Author: Remco van der Velde
 *  Version: 1.0
 */

namespace R3m\Io\Module;

use stdClass;
use R3m\Io\App;
use R3m\Io\Config;
use R3m\Io\Module\Data;

class Host {
    public const SCHEME_HTTP = 'http';
    public const SCHEME_HTTPS = 'https';
   
    public static function configure($object){
        if(defined('IS_CLI')){
            return $object;
        }
        $object->data('host.url', Host::url());
        $object->data('host.scheme', Host::scheme());
        $object->data('host.extension', Host::extension());
        $object->data('host.domain', Host::domain());
        
        $subdomain = Host::subdomain();
        
        $object->data('host.subdomain', $subdomain);
        
        $config = $object->data(App::NAMESPACE . '.' . Config::NAME);
            
        if(empty($subdomain)){
            $config->data(
                'host.dir.root',
                $config->data('project.dir.root') .
                'host' .
                $config->data('ds') .
                $object->data('host.domain') .
                $config->data('ds') .
                $object->data('host.extension') .
                $config->data('ds')
            );
            $config->data(
                'host.namespace',                
                'Host' .
                '\\' .
                ucfirst($object->data('host.domain')).
                '\\' .
                ucfirst($object->data('host.extension')) .
                '\\'
            );
            
        } else {
            $config->data(
                'host.dir.root',                
                $config->data('project.dir.root') .
                'host' .
                $config->data('ds') .
                $object->data('host.subdomain') .
                $config->data('ds') .
                $object->data('host.domain') .
                $config->data('ds') .
                $object->data('host.extension') .
                $config->data('ds')
            );
            $config->data(
                'host.namespace',
                'Host' .
                '\\' .
                ucfirst($object->data('host.subdomain')) . //per "." ucfirst ?
                '\\' .
                ucfirst($object->data('host.domain')).
                '\\' .
                ucfirst($object->data('host.extension')) .
                '\\'
            );
        }        
        $config->data(
            'host.dir.data',
            $config->data('host.dir.root') .
            $config->data('data') .
            $config->data('ds')             
        );       
        $config->data(
            'host.dir.cache',
            dirname($config->data('framework.dir.cache')) .
            $config->data('ds') .
            Config::HOST .
            $config->data('ds')
        );        
        $config->data(
            'host.dir.public',
            $config->data('host.dir.root') .            
            $config->data(Config::HTML) .
            $config->data('ds')
        );
        $config->data(
            'host.dir.source',
            $config->data('host.dir.root') .
            $config->data(Config::SOURCE) .
            $config->data('ds')
        );
        $config->data(
            'host.dir.view',
            $config->data('host.dir.root') .
            $config->data(Config::VIEW) .
            $config->data('ds')
        );
        /*
        $config->data(
            'host.dir.cli',
            $config->data('host.dir.root') .
            $config->data(Config::CLI) .
            $config->data('ds')
        );
        */      
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

    public static function extension($host=''){
        if(empty($host)){
            if(isset($_SERVER['HTTP_HOST'])){
                $host = $_SERVER['HTTP_HOST'];
            }
        }
        if(empty($host)){
            return false;
        }
        $explode = explode('.', $host);
        if(count($explode) > 1){
            return array_pop($explode);
        }
        return false;
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
}