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

use R3m\Io\App;
use R3m\Io\Config;

use R3m\Io\Module\Core;
use R3m\Io\Module\Event;
use R3m\Io\Module\File;
use R3m\Io\Module\Data;
use R3m\Io\Module\Controller;
use R3m\Io\Module\Parse;

use Exception;

use R3m\Io\Exception\FileWriteException;
use R3m\Io\Exception\ObjectException;
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
        if(
            !in_array(
                $command,
                Version::COMMAND,
                true
            )
        ){
            $exception = str_replace(
                Version::EXCEPTION_COMMAND_PARAMETER,
                $command,
                Version::EXCEPTION_COMMAND
            );
            $exception = new Exception($exception);
            Event::trigger($object, 'cli.' . strtolower(Version::NAME) . '.' . __FUNCTION__, [
                'command' => $command,
                'exception' => $exception
            ]);
            throw $exception;
        }
        $response = Version::{$command}($object);
        Event::trigger($object, 'cli.' . strtolower(Version::NAME) . '.' . __FUNCTION__, [
            'command' => $command
        ]);
        return $response;
    }

    private static function info(App $object){
        $name = false;
        $url = false;
        try {
            $name = Version::name(__FUNCTION__    , Version::NAME);
            $url = Version::locate($object, $name);
            $response = Version::response($object, $url);
            Event::trigger($object, 'cli.' . strtolower(Version::NAME) . '.' . __FUNCTION__, [
                'name' => $name,
                'url' => $url
            ]);
            return $response;
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            Event::trigger($object, 'cli.' . strtolower(Version::NAME) . '.' . __FUNCTION__, [
                'name' => $name,
                'url' => $url,
                'exception' => $exception
            ]);
            return $exception;
        }
    }

    /**
     * @throws ObjectException
     * @throws FileWriteException
     */
    private static function update(App $object){
        $config = $object->data(App::CONFIG);
        $config_url = $config->data(Config::DATA_FRAMEWORK_DIR_DATA) . Config::CONFIG;
        $url = false;
        $name = false;
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
            $response = Version::response($object, $url);
            Event::trigger($object, 'cli.' . strtolower(Version::NAME) . '.' . __FUNCTION__, [
                'name' => $name,
                'url' => $url
            ]);
            echo $response;
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            Event::trigger($object, 'cli.' . strtolower(Version::NAME) . '.' . __FUNCTION__, [
                'name' => $name,
                'url' => $url,
                'exception' => $exception
            ]);
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
        return null;
    }

}