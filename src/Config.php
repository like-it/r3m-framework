<?php

namespace R3m\Io;

use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;

class Config extends Data {
    public const NAME = 'Config';

    public const MODE_PRODUCTION = 'production';
    public const MODE_DEVELOPMENT = 'development';

    public const DATA = 'data';
    public const VALUE_DATA = 'Data';

    public const PUBLIC = 'public';
    public const VALUE_PUBLIC = 'Public';

    public const HOST = 'host';
    public const VALUE_HOST = 'Host';

    public const CACHE = 'cache';
    public const VALUE_CACHE = '/tmp/r3m/io/';

    public const SOURCE = 'Source';
    public const VALUE_SOURCE = 'src';

    public const CLI = 'cli';
    public const VALUE_CLI = 'Cli';

    public const MODULE = 'module';
    public const VALUE_MODULE = 'Module';

    public const FRAMEWORK = 'framework';
    public const VALUE_FRAMEWORK = 'r3m/framework';

    public const ENVIRONMENT = 'environment';
    public const VALUE_ENVIRONMENT = Config::MODE_PRODUCTION;

    public const PLUGIN = 'plugin';
    public const VALUE_PLUGIN = 'Plugin';

    public const DS = 'ds';
    public const VALUE_DS = DIRECTORY_SEPARATOR;

    public const VIEW = 'view';
    public const VALUE_VIEW = 'View';

    public const LOCALHOST_EXTENSION = 'localhost.extension';
    public const VALUE_LOCALHOST_EXTENSION =  [
        'local',
        'develop'
    ];

    public const ROUTE = 'Route.json';
    public const CONFIG = 'Config.json';

    public const DICTIONARY = 'dictionary';

    public const DATA_DIR_VENDOR = 'dir.vendor';
    public const DATA_FRAMEWORK_VERSION = 'framework.version';
    public const DATA_FRAMEWORK_BUILT = 'framework.built';
    public const DATA_FRAMEWORK_DIR = 'framework.dir';
    public const DATA_FRAMEWORK_DIR_ROOT = Config::DATA_FRAMEWORK_DIR . '.' .'root';
    public const DATA_FRAMEWORK_DIR_VENDOR = Config::DATA_FRAMEWORK_DIR . '.' .'vendor';
    public const DATA_FRAMEWORK_DIR_SOURCE = Config::DATA_FRAMEWORK_DIR . '.' .'source';
    public const DATA_FRAMEWORK_DIR_DATA = Config::DATA_FRAMEWORK_DIR . '.' .'data';
    public const DATA_FRAMEWORK_DIR_CLI = Config::DATA_FRAMEWORK_DIR . '.' .'cli';
    public const DATA_FRAMEWORK_DIR_CACHE = Config::DATA_FRAMEWORK_DIR . '.' .'cache';
    public const DATA_FRAMEWORK_DIR_MODULE = Config::DATA_FRAMEWORK_DIR . '.' .'module';

    public const DATA_FRAMEWORK_ENVIRONMENT = 'framework.environment';

    public const DATA_PROJECT_ROUTE_FILENAME = 'project.route.filename';
    public const DATA_PROJECT_ROUTE_URL = 'project.route.url';
    public const DATA_PROJECT_DIR = 'project.dir';
    public const DATA_PROJECT_DIR_ROOT =  Config::DATA_PROJECT_DIR . '.' . 'root';
    public const DATA_PROJECT_DIR_VENDOR =  Config::DATA_PROJECT_DIR . '.' . 'vendor';
    public const DATA_PROJECT_DIR_SOURCE =  Config::DATA_PROJECT_DIR . '.' . 'source';
    public const DATA_PROJECT_DIR_DATA =  Config::DATA_PROJECT_DIR . '.' . 'data';
    public const DATA_PROJECT_DIR_CLI =  Config::DATA_PROJECT_DIR . '.' . 'cli';
    public const DATA_PROJECT_DIR_PUBLIC =  Config::DATA_PROJECT_DIR . '.' . 'public';
    public const DATA_PROJECT_DIR_HOST =  Config::DATA_PROJECT_DIR . '.' . 'host';

    public function __construct($config=[]){
        if(array_key_exists(Config::DATA_DIR_VENDOR, $config)){
            $this->data(Config::DATA_PROJECT_DIR_VENDOR, $config[Config::DATA_DIR_VENDOR]);
            $this->data(Config::DATA_PROJECT_DIR_ROOT, dirname($this->data(Config::DATA_PROJECT_DIR_VENDOR)) . '/');
            unset($config[Config::DATA_DIR_VENDOR]);
        }
        $this->default();
        $url = $this->data(Config::DATA_FRAMEWORK_DIR_DATA) . Config::CONFIG;
        if(File::exist($url)){
            $read = Core::object(File::read($url));
            $this->data(Core::object_merge($this->data(), $read));
        }
        //maybe merge this too...
        foreach($config as $attribute => $value){
            $this->data($attribute, $value);
        }
    }

    public function default(){
        $key = Config::DICTIONARY . '.' . Config::DATA;
        $value = Config::VALUE_DATA;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::SOURCE;
        $value = Config::VALUE_SOURCE;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::CACHE;
        $value = Config::VALUE_CACHE;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::PUBLIC;
        $value = Config::VALUE_PUBLIC;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::MODULE;
        $value = Config::VALUE_MODULE;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::CLI;
        $value = Config::VALUE_CLI;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::HOST;
        $value = Config::VALUE_HOST;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::VIEW;
        $value = Config::VALUE_VIEW;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::FRAMEWORK;
        $value = Config::VALUE_FRAMEWORK;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::ENVIRONMENT;
        $value = Config::VALUE_ENVIRONMENT;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::PLUGIN;
        $value = Config::VALUE_PLUGIN;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::DS;
        $value = Config::VALUE_DS;
        $this->data($key, $value);

        $value = Config::VALUE_DS;
        $key = Config::DS;
        $this->data($key, $value);

        $this->data('extension.php', '.php');
        $this->data('extension.json', '.json');
        $this->data('extension.css', '.css');
        $this->data('extension.js', '.js');
        $this->data('extension.jpg', '.jpg');
        $this->data('extension.gif', '.gif');
        $this->data('extension.png', '.png');
        $this->data('extension.zip', '.zip');
        $this->data('extension.rar', '.rar');
        $this->data('extension.tpl', '.tpl');

        $this->data(Config::LOCALHOST_EXTENSION, Config::VALUE_LOCALHOST_EXTENSION);

        $key = Config::DATA_PROJECT_DIR_SOURCE;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_ROOT) .
            $this->data(Config::DICTIONARY . '.' . Config::SOURCE) .
            $this->data(Config::DS);
        $this->data($key, $value);

        $key = Config::DATA_PROJECT_DIR_DATA;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_ROOT) .
            $this->data(Config::DICTIONARY . '.' . Config::DATA) .
            $this->data(Config::DS);
        $this->data($key, $value);

        $key = Config::DATA_PROJECT_DIR_CLI;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_ROOT) .
            $this->data(Config::DICTIONARY . '.' . Config::CLI) .
            $this->data(Config::DS);
        $this->data($key, $value);

        $key = Config::DATA_PROJECT_DIR_PUBLIC;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_ROOT) .
            $this->data(Config::DICTIONARY . '.' . Config::PUBLIC) .
            $this->data(Config::DS);
        $this->data($key, $value);

        $key = Config::DATA_PROJECT_DIR_HOST;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_ROOT) .
            $this->data(Config::DICTIONARY . '.' . Config::HOST) .
            $this->data(Config::DS);
        $this->data($key, $value);

        $key = Config::DATA_PROJECT_ROUTE_FILENAME;
        $value = Config::ROUTE;
        $this->data($key, $value);

        //project.route.url can be configured in index / cli

        $key = Config::DATA_FRAMEWORK_DIR_ROOT;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_VENDOR) .
            $this->data(Config::DICTIONARY . '.' . Config::FRAMEWORK) .
            $this->data(Config::DS);
        $this->data($key, $value);

        $key = Config::DATA_FRAMEWORK_DIR_SOURCE;
        $value =
            $this->data(Config::DATA_FRAMEWORK_DIR_ROOT) .
            $this->data(Config::DICTIONARY . '.' . Config::SOURCE) .
            $this->data(Config::DS);
        $this->data($key, $value);

        $key = Config::DATA_FRAMEWORK_DIR_DATA;
        $value =
            $this->data(Config::DATA_FRAMEWORK_DIR_ROOT) .
            $this->data(Config::DICTIONARY . '.' . Config::DATA) .
            $this->data(Config::DS);
        $this->data($key, $value);

        $key = Config::DATA_FRAMEWORK_DIR_CACHE;
        $value =
            $this->data(Config::DICTIONARY . '.' . Config::CACHE) .
            $this->data(Config::DICTIONARY . '.' . Config::FRAMEWORK) .
            $this->data(Config::DS);
        $this->data($key, $value);

        $key = Config::DATA_FRAMEWORK_DIR_MODULE;
        $value =
            $this->data(Config::DATA_FRAMEWORK_DIR_SOURCE) .
            $this->data(Config::DICTIONARY . '.' . Config::MODULE) .
            $this->data(Config::DS);
        $this->data($key, $value);

        $key = Config::DATA_FRAMEWORK_DIR_CLI;
        $value =
            $this->data(Config::DATA_FRAMEWORK_DIR_ROOT) .
            $this->data(Config::DICTIONARY . '.' . Config::CLI) .
            $this->data(Config::DS);
        $this->data($key, $value);

        $key = Config::DATA_FRAMEWORK_ENVIRONMENT;
        $value = $this->data(Config::DICTIONARY . '.' . Config::ENVIRONMENT);
        $this->data($key, $value);
    }

    public static function ucfirst_sentence($string='', $delimiter='.'){
        $explode = explode($delimiter, $string);
        foreach($explode as $nr => $part){
            $explode[$nr] = ucfirst(trim($part));
        }
        return implode($delimiter, $explode);
    }
}