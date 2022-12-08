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
namespace R3m\Io;

use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;

class Config extends Data {
    const DIR = __DIR__ . '/';
    const NAME = 'Config';

    const MODE_DEVELOPMENT = 'development';
    const MODE_PRODUCTION = 'production';
    const MODE_STAGING = 'staging';

    const DATA = 'data';
    const VALUE_DATA = 'Data';

    const PUBLIC = 'public';
    const VALUE_PUBLIC = 'Public';

    const HOST = 'host';
    const VALUE_HOST = 'Host';

    const CACHE = 'cache';
    const VALUE_CACHE = '/tmp/r3m/io/';

    const SOURCE = 'Source';
    const VALUE_SOURCE = 'src';

    const BINARY = 'Binary';
    const VALUE_BINARY = 'Bin';

    const CLI = 'cli';
    const VALUE_CLI = 'Cli';

    const MODULE = 'module';
    const VALUE_MODULE = 'Module';

    const FRAMEWORK = 'framework';
    const VALUE_FRAMEWORK = 'r3m/framework';

    const ENVIRONMENT = 'environment';
    const VALUE_ENVIRONMENT = Config::MODE_PRODUCTION;

    const FUNCTION = 'function';
    const VALUE_FUNCTION = 'Function';

    const PLUGIN = 'plugin';
    const VALUE_PLUGIN = 'Plugin';

    const CONTROLLER = 'controller';
    const VALUE_CONTROLLER = 'Controller';

    const VALIDATE = 'validate';
    const VALUE_VALIDATE = 'Validate';

    const DS = 'ds';
    const VALUE_DS = DIRECTORY_SEPARATOR;

    const VIEW = 'view';
    const VALUE_VIEW = 'View';

    const MODEL = 'model';
    const VALUE_MODEL = 'Model';

    const COMPONENT = 'component';
    const VALUE_COMPONENT = 'Component';

    const ENTITY = 'entity';
    const VALUE_ENTITY = 'Entity';

    const REPOSITORY = 'repository';
    const VALUE_REPOSITORY = 'Repository';

    const SERVICE = 'service';
    const VALUE_SERVICE = 'Service';

    const NODE = 'node';
    const VALUE_NODE = 'Node';

    const TRANSLATION = 'translation';
    const VALUE_TRANSLATION = 'Translation';

    const LOCALHOST_EXTENSION = 'localhost.extension';
    const VALUE_LOCALHOST_EXTENSION =  [
        'local'
    ];

    const LOG = 'log';
    const VALUE_LOG = 'Log';

    const EXECUTE = 'execute';
    const VALUE_EXECUTE = 'Execute';

    const ROUTE = 'Route.json';
    const CONFIG = 'Config.json';

    const DICTIONARY = 'dictionary';

    const DATA_PDO = 'pdo';

    const DATA_DIR_VENDOR = 'dir.vendor';
    const DATA_FRAMEWORK_VERSION = 'framework.version';
    const DATA_FRAMEWORK_BUILT = 'framework.built';
    const DATA_FRAMEWORK_DIR = 'framework.dir';
    const DATA_FRAMEWORK_DIR_ROOT = Config::DATA_FRAMEWORK_DIR . '.' .'root';
    const DATA_FRAMEWORK_DIR_VENDOR = Config::DATA_FRAMEWORK_DIR . '.' .'vendor';
    const DATA_FRAMEWORK_DIR_SOURCE = Config::DATA_FRAMEWORK_DIR . '.' .'source';
    const DATA_FRAMEWORK_DIR_DATA = Config::DATA_FRAMEWORK_DIR . '.' .'data';
    const DATA_FRAMEWORK_DIR_CLI = Config::DATA_FRAMEWORK_DIR . '.' .'cli';
    const DATA_FRAMEWORK_DIR_CACHE = Config::DATA_FRAMEWORK_DIR . '.' .'cache';
    const DATA_FRAMEWORK_DIR_MODULE = Config::DATA_FRAMEWORK_DIR . '.' .'module';
    const DATA_FRAMEWORK_DIR_PLUGIN =  Config::DATA_FRAMEWORK_DIR . '.' . 'plugin';
    const DATA_FRAMEWORK_DIR_FUNCTION =  Config::DATA_FRAMEWORK_DIR . '.' . 'function';
    const DATA_FRAMEWORK_DIR_VALIDATE =  Config::DATA_FRAMEWORK_DIR . '.' . 'validate';
    const DATA_FRAMEWORK_DIR_VIEW =  Config::DATA_FRAMEWORK_DIR . '.' . 'view';

    const DATA_FRAMEWORK_ENVIRONMENT = 'framework.environment';

    const DATA_HOST_DIR = 'host.dir';
    const DATA_HOST_DIR_ROOT = Config::DATA_HOST_DIR . '.' . 'root';
    const DATA_HOST_DIR_CACHE = Config::DATA_HOST_DIR . '.' . 'cache';
    const DATA_HOST_DIR_DATA = Config::DATA_HOST_DIR . '.' . 'data';
    const DATA_HOST_DIR_PUBLIC = Config::DATA_HOST_DIR . '.' . 'public';
    const DATA_HOST_DIR_PLUGIN = Config::DATA_HOST_DIR . '.' . 'plugin';
    const DATA_HOST_DIR_PLUGIN_2 = Config::DATA_HOST_DIR . '.' . 'plugin-2';

    const DATA_PARSE_DIR = 'parse.dir';
    const DATA_PARSE_DIR_TEMPLATE = Config::DATA_PARSE_DIR . '.' . 'template';
    const DATA_PARSE_DIR_COMPILE = Config::DATA_PARSE_DIR . '.' . 'compile';
    const DATA_PARSE_DIR_CACHE = Config::DATA_PARSE_DIR . '.' . 'cache';
    const DATA_PARSE_DIR_PLUGIN = Config::DATA_PARSE_DIR . '.' . 'plugin';

    const DATA_PROJECT_ROUTE_FILENAME = 'project.route.filename';
    const DATA_PROJECT_ROUTE_URL = 'project.route.url';
    const DATA_PROJECT_DIR = 'project.dir';
    const DATA_PROJECT_DIR_ROOT =  Config::DATA_PROJECT_DIR . '.' . 'root';
    const DATA_PROJECT_DIR_BINARY =  Config::DATA_PROJECT_DIR . '.' . 'binary';
    const DATA_PROJECT_DIR_VENDOR =  Config::DATA_PROJECT_DIR . '.' . 'vendor';
    const DATA_PROJECT_DIR_SOURCE =  Config::DATA_PROJECT_DIR . '.' . 'source';
    const DATA_PROJECT_DIR_DATA =  Config::DATA_PROJECT_DIR . '.' . 'data';
    const DATA_PROJECT_DIR_CLI =  Config::DATA_PROJECT_DIR . '.' . 'cli';
    const DATA_PROJECT_DIR_PUBLIC =  Config::DATA_PROJECT_DIR . '.' . 'public';
    const DATA_PROJECT_DIR_HOST =  Config::DATA_PROJECT_DIR . '.' . 'host';
    const DATA_PROJECT_DIR_PLUGIN =  Config::DATA_PROJECT_DIR . '.' . 'plugin';
    const DATA_PROJECT_DIR_FUNCTION =  Config::DATA_PROJECT_DIR . '.' . 'function';
    const DATA_PROJECT_DIR_LOG =  Config::DATA_PROJECT_DIR . '.' . 'log';
    const DATA_PROJECT_DIR_EXECUTE =  Config::DATA_PROJECT_DIR . '.' . 'execute';

    const DATA_CONTROLLER = 'controller';
    const DATA_CONTROLLER_CLASS = 'controller.class';
    const DATA_CONTROLLER_NAME = 'controller.name';
    const DATA_CONTROLLER_TITLE = 'controller.title';
    const DATA_CONTROLLER_DIR = 'controller.dir';
    const DATA_CONTROLLER_DIR_ROOT = Config::DATA_CONTROLLER_DIR . '.' .'root';
    const DATA_CONTROLLER_DIR_SOURCE = Config::DATA_CONTROLLER_DIR . '.' .'source';
    const DATA_CONTROLLER_DIR_DATA = Config::DATA_CONTROLLER_DIR . '.' .'data';
    const DATA_CONTROLLER_DIR_PLUGIN = Config::DATA_CONTROLLER_DIR . '.' .'plugin';
    const DATA_CONTROLLER_DIR_FUNCTION = Config::DATA_CONTROLLER_DIR . '.' .'function';
    const DATA_CONTROLLER_DIR_MODEL = Config::DATA_CONTROLLER_DIR . '.' .'model';
    const DATA_CONTROLLER_DIR_ENTITY = Config::DATA_CONTROLLER_DIR . '.' .'entity';
    const DATA_CONTROLLER_DIR_REPOSITORY = Config::DATA_CONTROLLER_DIR . '.' .'repository';
    const DATA_CONTROLLER_DIR_EXECUTE = Config::DATA_CONTROLLER_DIR . '.' .'execute';
    const DATA_CONTROLLER_DIR_SERVICE = Config::DATA_CONTROLLER_DIR . '.' .'service';
    const DATA_CONTROLLER_DIR_NODE = Config::DATA_CONTROLLER_DIR . '.' .'node';
    const DATA_CONTROLLER_DIR_VIEW = Config::DATA_CONTROLLER_DIR . '.' .'view';
    const DATA_CONTROLLER_DIR_COMPONENT = Config::DATA_CONTROLLER_DIR . '.' .'component';
    const DATA_CONTROLLER_DIR_PUBLIC = Config::DATA_CONTROLLER_DIR . '.' .'public';

    const DATA_ROUTE = 'route';
    const DATA_ROUTE_PREFIX = Config::DATA_ROUTE . '.' . 'prefix';

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
        foreach($config as $attribute => $value){
            $this->data($attribute, $value);
        }
    }

    /**
     * @throws Exception\ObjectException
     * @throws Exception\FileWriteException
     */
    public static function configure(App $object){
        $config = $object->data(App::CONFIG);
        $url = $config->data(Config::DATA_PROJECT_DIR_DATA) . Config::CONFIG;
        if(File::exist($url)){
            $read = Core::object(File::read($url));
            $config->data(Core::object_merge($config->data(), $read));
        }
    }

    public function default(){
        $key = Config::DICTIONARY . '.' . Config::DATA;
        $value = Config::VALUE_DATA;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::SOURCE;
        $value = Config::VALUE_SOURCE;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::BINARY;
        $value = Config::VALUE_BINARY;
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

        $key = Config::DICTIONARY . '.' . Config::LOG;
        $value = Config::VALUE_LOG;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::EXECUTE;
        $value = Config::VALUE_EXECUTE;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::COMPONENT;
        $value = Config::VALUE_COMPONENT;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::SERVICE;
        $value = Config::VALUE_SERVICE;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::ENTITY;
        $value = Config::VALUE_ENTITY;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::REPOSITORY;
        $value = Config::VALUE_REPOSITORY;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::VIEW;
        $value = Config::VALUE_VIEW;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::MODEL;
        $value = Config::VALUE_MODEL;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::CONTROLLER;
        $value = Config::VALUE_CONTROLLER;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::FRAMEWORK;
        $value = Config::VALUE_FRAMEWORK;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::ENVIRONMENT;
        $value = Config::VALUE_ENVIRONMENT;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::FUNCTION;
        $value = Config::VALUE_FUNCTION;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::PLUGIN;
        $value = Config::VALUE_PLUGIN;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::VALIDATE;
        $value = Config::VALUE_VALIDATE;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::DS;
        $value = Config::VALUE_DS;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::TRANSLATION;
        $value = Config::VALUE_TRANSLATION;
        $this->data($key, $value);

        $key = Config::DICTIONARY . '.' . Config::NODE;
        $value = Config::VALUE_NODE;
        $this->data($key, $value);

        $value = Config::VALUE_DS;
        $key = Config::DS;
        $this->data($key, $value);
        $this->data(Config::LOCALHOST_EXTENSION, Config::VALUE_LOCALHOST_EXTENSION);

        $key = Config::DATA_PROJECT_DIR_SOURCE;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_ROOT) .
            $this->data(Config::DICTIONARY . '.' . Config::SOURCE) .
            $this->data(Config::DS);
        $this->data($key, $value);

        $key = Config::DATA_PROJECT_DIR_BINARY;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_ROOT) .
            $this->data(Config::DICTIONARY . '.' . Config::BINARY) .
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

        $key = Config::DATA_PROJECT_DIR_LOG;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_ROOT) .
            $this->data(Config::DICTIONARY . '.' . Config::LOG) .
            $this->data(Config::DS);
        $this->data($key, $value);

        $key = Config::DATA_PROJECT_DIR_EXECUTE;
        $value =
            $this->data(Config::DATA_PROJECT_DIR_ROOT) .
            $this->data(Config::DICTIONARY . '.' . Config::EXECUTE) .
            $this->data(Config::DS);
        $this->data($key, $value);

        $key = Config::DATA_PROJECT_ROUTE_FILENAME;
        $value = Config::ROUTE;
        $this->data($key, $value);

        //project.route.url can be configured in index / cli

        $dir = Dir::name(Config::DIR);
        $key = Config::DATA_FRAMEWORK_DIR_ROOT;
        $value = $dir;
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
            $this->data(Config::DATA_FRAMEWORK_DIR_SOURCE) .
            $this->data(Config::DICTIONARY . '.' . Config::CLI) .
            $this->data(Config::DS);
        $this->data($key, $value);

        $key = Config::DATA_FRAMEWORK_DIR_VALIDATE;
        $value =
            $this->data(Config::DATA_FRAMEWORK_DIR_SOURCE) .
            $this->data(Config::DICTIONARY . '.' . Config::VALIDATE) .
            $this->data(Config::DS);
        $this->data($key, $value);

        $key = Config::DATA_FRAMEWORK_DIR_VIEW;
        $value =
            $this->data(Config::DATA_FRAMEWORK_DIR_SOURCE) .
            $this->data(Config::DICTIONARY . '.' . Config::VIEW) .
            $this->data(Config::DS);
        $this->data($key, $value);
        $key = Config::DATA_FRAMEWORK_ENVIRONMENT;
        $value = $this->data(Config::DICTIONARY . '.' . Config::ENVIRONMENT);
        $this->data($key, $value);
    }
}