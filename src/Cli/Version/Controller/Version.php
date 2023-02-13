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
namespace R3m\Io\Cli\Version\Controller;

use Exception;
use R3m\Io\App;
use R3m\Io\Config;
use R3m\Io\Module\Core;
use R3m\Io\Module\File;
use R3m\Io\Module\Data;
use R3m\Io\Module\Controller;
use R3m\Io\Module\Parse;

use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;

class Version extends Controller {
    const NAME = 'Version';
    const DIR = __DIR__;

    const COMMAND_INFO = 'info';
    const COMMAND_UPDATE = 'update';
    const COMMAND = [
        Version::COMMAND_INFO,
        Version::COMMAND_UPDATE
    ];

    const DEFAULT_COMMAND = Version::COMMAND_INFO;

    const UPDATE_COMMAND = [
        '{{binary()}} version info',
    ];

    const INFO = '{{binary()}} version                        | Version information';
    const INFO_RUN = [
        '{{binary()}} version                        | Version information'
    ];
    const INFO_UPDATE = [
        '{{binary()}} version update                 | Version update, optional parameters'
    ];

    const DATA_FRAMEWORK_VERSION = 'framework.version';
    const DATA_FRAMEWORK_BUILT = 'framework.built';
    const DATA_FRAMEWORK_MAJOR = 'framework.major';
    const DATA_FRAMEWORK_MINOR = 'framework.minor';
    const DATA_FRAMEWORK_PATCH = 'framework.patch';

    const EXCEPTION_COMMAND_PARAMETER = '{$command}';
    const EXCEPTION_COMMAND = 'invalid command (' . Version::EXCEPTION_COMMAND_PARAMETER . ')' . PHP_EOL;

    /**
     * @throws Exception
     */
    public static function run(App $object){
        $command = $object->parameter($object, Version::NAME, 1);
        if($command === null){
            $command = Version::DEFAULT_COMMAND;
        }

        if(!in_array($command, Version::COMMAND)){
            $exception = str_replace(
                Version::EXCEPTION_COMMAND_PARAMETER,
                $command,
                Version::EXCEPTION_COMMAND
            );
            throw new Exception($exception);
        }
        return Version::{$command}($object);
    }

    private static function info(App $object){
        try {
            $name = Version::name(__FUNCTION__    , Version::NAME);
            $url = Version::locate($object, $name);
            return Version::response($object, $url);
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            return $exception;
        }
    }

    private static function update(App $object){
        $config = $object->data(App::CONFIG);
        $config_url = $config->data(Config::DATA_FRAMEWORK_DIR_DATA) . Config::CONFIG;
        if(File::exist($config_url)){
            $read = Core::object(File::read($config_url));
            $data = new Data($read);
            $version = $object->parameter($object, Version::COMMAND_UPDATE, 1);
            if($version === null){
                $data->data(Version::DATA_FRAMEWORK_PATCH, intval($data->data(Version::DATA_FRAMEWORK_PATCH)) + 1);
            } else {
                $explode = explode('.', $version, 3);
                if(isset($explode[0])){
                    $data->data(Version::DATA_FRAMEWORK_MAJOR, $explode[0]);
                }
                if(isset($explode[1])){
                    $data->data(Version::DATA_FRAMEWORK_MINOR, $explode[1]);
                }
                if(isset($explode[2])){
                    $data->data(Version::DATA_FRAMEWORK_PATCH, $explode[2]);
                }
            }
            $data->data(Version::DATA_FRAMEWORK_VERSION, $data->data(Version::DATA_FRAMEWORK_MAJOR) . '.' . $data->data(Version::DATA_FRAMEWORK_MINOR) . '.' . ($data->data(Version::DATA_FRAMEWORK_PATCH)));
            $data->data(Version::DATA_FRAMEWORK_BUILT, date('Y-m-d H:i:s'));
            $write = Core::object($data->data(), 'json');
            File::write($config_url, $write);
        }
        try {
            $name = Version::name(__FUNCTION__    , Version::NAME);
            $url = Version::locate($object, $name);
            echo Version::response($object, $url);
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            return $exception;
        }
        $parse = new Parse($object);
        $command = Version::UPDATE_COMMAND;

        foreach($command as $record){
            $execute = $parse->compile($record);
            echo 'Executing: ' . $execute . '...' . PHP_EOL;
            $output = [];
            Core::execute($object, $execute, $output);
            $output[] = '';
            echo implode(PHP_EOL, $output);
        }
    }
}