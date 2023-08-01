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
namespace R3m\Io\Cli\Configure\Controller;

use R3m\Io\App;
use R3m\Io\Exception\FileWriteException;
use R3m\Io\Exception\ObjectException;
use R3m\Io\Module\Controller;

use Exception;

use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;
use R3m\Io\Module\Dir;
use R3m\Io\Module\Event;
use R3m\Io\Module\File;

class Configure extends Controller {
    const DIR = __DIR__;
    const NAME = 'Configure';
    const MODULE_INFO = 'Info';
    const INFO = '{{binary()}} configure                      | App configuration commands';
    const INFO_RUN = [
        '{{binary()}} configure                      | App configuration commands',
        '{{binary()}} configure cors info            | Cors requests information',
        '{{binary()}} configure domain add           | Adds a domain to /project_dir/Host',
        '{{binary()}} configure environment toggle   | Toggle environment between development, staging & production',
        '{{binary()}} configure host add             | Adds a host to /etc/host',
        '{{binary()}} configure host setup           | Setup an apache2 site',
        '{{binary()}} configure host delete          | Delete a host from /etc/host',
        '{{binary()}} configure public create        | Creates the public html directory',
        '{{binary()}} configure route resource       | Add a main route based on resource',
        '{{binary()}} configure route delete         | Delete a main route based on resource',
        '{{binary()}} configure server admin         | Set the server admin',
        '{{binary()}} configure server url           | Sets an url for different environments',
        '{{binary()}} configure site create          | Create an apache2 site file',
        '{{binary()}} configure site delete          | Delete an apache2 site file',
        '{{binary()}} configure site disable         | Disable an apache2 site',
        '{{binary()}} configure site enable          | Enable an apache2 site'
    ];

    private static function scan(App $object): array
    {
        $scan = [
            'module' => [],
            'submodule' => [],
            'command' => [],
            'subcommand' => []
        ];
        $url = $object->config('controller.dir.view');
        if(!Dir::exist($url)){
            return $scan;
        }
        $dir = new Dir();
        $read = $dir->read($url, true);
        if(!$read){
            return $scan;
        }

        foreach($read as $nr => $file){
            if($file->type !== File::TYPE){
                continue;
            }
            $part = substr($file->url, strlen($url));
            $explode = explode('/', $part, 2);
            $submodule = false;
            $command = false;
            $subcommand = false;

            if(array_key_exists(1, $explode)){
                $module = strtolower($explode[0]);
                $temp = explode('.', $explode[1]);
                array_pop($temp);
                $submodule = strtolower($temp[0]);
                if(array_key_exists(1, $temp)){
                    $command = strtolower($temp[1]);
                }
                if(array_key_exists(2, $temp)){
                    $subcommand = strtolower($temp[1]);
                }
            } else {
                $temp = explode('.', $explode[0]);
                array_pop($temp);
                $module = strtolower($temp[0]);
                if(array_key_exists(1, $temp)){
                    $submodule = strtolower($temp[1]);
                }
                if(array_key_exists(2, $temp)){
                    $command = strtolower($temp[1]);
                }
                if(array_key_exists(3, $temp)){
                    $subcommand = strtolower($temp[1]);
                }
            }
            if(
                !in_array(
                    $module,
                    $scan['module'],
                    true
                )
            ){
                $scan['module'][] = $module;
            }
            if(
                $submodule &&
                !in_array(
                    $submodule,
                    $scan['submodule'],
                    true
                )
            ){
                $scan['submodule'][] = $submodule;
            }
            if(
                $command  &&
                !in_array(
                    $command,
                    $scan['command'],
                    true
                )
            ){
                $scan['command'][] = $command;
            }
            if(
                $subcommand &&
                !in_array(
                    $subcommand,
                    $scan['subcommand'],
                    true
                )
            ){
                $scan['subcommand'][] = $subcommand;
            }
        }
        return $scan;
    }

    /**
     * @throws LocateException
     * @throws ObjectException
     * @throws FileWriteException
     * @throws UrlEmptyException
     * @throws UrlNotExistException
     */
    public static function run(App $object){
        $url = false;
        $scan = Configure::scan($object);
        $module = $object->parameter($object, 'configure', 1);
        if(!in_array($module, $scan['module'], true)){
            $module = Configure::MODULE_INFO;
        }
        $submodule = $object->parameter($object, 'configure', 2);
        if(
            !in_array(
                $submodule,
                $scan['submodule'],
                true
            )
        ){
            if($module === Configure::MODULE_INFO){
                $submodule = false;
            } else {
                $submodule = Configure::MODULE_INFO;
            }
        }
        $command = $object->parameter($object, 'configure', 3);
        if(
            !in_array(
                $command,
                $scan['command'],
                true
            ) ||
            $module === Configure::MODULE_INFO ||
            $submodule === Configure::MODULE_INFO
        ){
            $command = false;
        }
        $subcommand = $object->parameter($object, 'configure', 4);
        if(
            !in_array(
                $subcommand,
                $scan['subcommand'],
                true
            ) ||
            $module === Configure::MODULE_INFO ||
            $submodule === Configure::MODULE_INFO
        ){
            $subcommand = false;
        }
        try {
            if(
                !empty($submodule) &&
                !empty($command) &&
                !empty($subcommand)
            ){
                $url = Configure::locate(
                    $object,
                    ucfirst($module) .
                    '.' .
                    ucfirst($submodule) .
                    '.' .
                    ucfirst($command) .
                    '.' .
                    ucfirst($subcommand)
                );
            }
            elseif(
                !empty($submodule) &&
                !empty($command)
            ){
                $url = Configure::locate(
                    $object,
                    ucfirst($module) .
                    '.' .
                    ucfirst($submodule) .
                    '.' .
                    ucfirst($command)
                );
            }
            elseif(!empty($submodule)){
                $url = Configure::locate(
                    $object,
                    ucfirst($module) .
                    '.' .
                    ucfirst($submodule)
                );
            } else {
                $url = Configure::locate(
                    $object,
                    ucfirst($module)
                );
            }
            $response = Configure::response($object, $url);
            Event::trigger($object, 'cli.' . strtolower(Configure::NAME) . '.' . __FUNCTION__, [
                'module' => $module,
                'submodule' => $submodule,
                'command' => $command,
                'subcommand' => $subcommand,
                'url' => $url
            ]);
            return $response;
        } catch (Exception | UrlEmptyException | UrlNotExistException | LocateException $exception){
            Event::trigger($object, 'cli.' . strtolower(Configure::NAME) . '.' . __FUNCTION__, [
                'module' => $module,
                'submodule' => $submodule,
                'command' => $command,
                'subcommand' => $subcommand,
                'url' => $url,
                'exception', $exception
            ]);
            return $exception;
        }
    }
}