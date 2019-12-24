<?php
/**
 * @author         Remco van der Velde
 * @since         2016-10-19
 * @version        1.0
 * @changeLog
 *     -    all
 */
namespace R3m\Io\Cli\Version\Controller;

use Exception;
use R3m\Io\App;
use R3m\Io\Config;
use R3m\Io\Module\Core;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;
use R3m\Io\Module\Data;
use R3m\Io\Module\View;
use R3m\Io\Module\Parse;

class Version extends View{
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
            '{binary()} version info',
    ];

    const EXCEPTION_COMMAND_PARAMETER = '{$command}';
    const EXCEPTION_COMMAND = 'invalid command (' . Version::EXCEPTION_COMMAND_PARAMETER . ')';

    public static function run($object){
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

    private static function info($object){
        $url = Version::locate($object, ucfirst(__FUNCTION__));
        return Version::view($object, $url);
    }


    private static function update($object){
        $config = $object->data(App::DATA_CONFIG);
        $config_url = $config->data('framework.dir.data') . Config::CONFIG;
        if(File::exist($config_url)){
            $read = Core::object(File::read($config_url));
            $data = new Data($read);
            $version = $object->parameter($object, Version::COMMAND_UPDATE, 1);
            if($version === null){
                $data->data('framework.patch', $data->data('framework.patch') + 1);
            } else {
                $explode = explode('.', $version, 3);
                if(isset($explode[0])){
                    $data->data('framework.major', $explode[0]);
                }
                if(isset($explode[1])){
                    $data->data('framework.minor', $explode[1]);
                }
                if(isset($explode[2])){
                    $data->data('framework.patch', $explode[2]);
                }
            }
            $data->data('framework.version', $data->data('framework.major') . '.' . $data->data('framework.minor') . '.' . ($data->data('framework.patch')));
            $data->data('framework.built', date('Y-m-d H:i:s'));
            $write = Core::object($data->data(), 'json');
            File::write($config_url, $write);
        }
        $url = Version::locate($object, ucfirst(__FUNCTION__));
        echo Version::view($object, $url);

        $parse = new Parse($object);
        $command = VERSION::UPDATE_COMMAND;

        foreach($command as $record){
            $execute = $parse->compile($record);
            echo 'Executing: ' . $execute . "...\n";
            $output = [];
            Core::execute($execute, $output);
            $output[] = '';
            echo implode("\n", $output);
        }
    }
}