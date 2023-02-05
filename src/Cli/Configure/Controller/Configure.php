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
use R3m\Io\Module\Controller;

use Exception;

use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;

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

    public static function run(App $object){
        $module = $object->parameter($object, 'configure', 1);
        if(empty($module)){
            $module = Configure::MODULE_INFO;
        }
        $sub_module = $object->parameter($object, 'configure', 2);
        $command = $object->parameter($object, 'configure', 3);
        if(
            (
                substr($command, 0, 1) === '[' &&
                substr($command, -1, 1) === ']'
            ) ||
            is_numeric($command) ||
            in_array(
                $command,
                [
                    'true',
                    'false',
                    'null'
                ]
            )
        ){
            $command = null;
        }
        elseif(
            substr($command, 0, 1) === '{' &&
            substr($command, -1, 1) === '}'
        ){
            $command = null;
        }
        try {
            if(
                !empty($command) &&
                !empty($sub_module)
            ){
                $url = Configure::locate($object, ucfirst($module) . '.' . ucfirst($sub_module) . '.' . ucfirst($command));
            }
            elseif(!empty($sub_module)){
                $url = Configure::locate($object, ucfirst($module) . '.' . ucfirst($sub_module));
            } else {
                $url = Configure::locate($object, ucfirst($module));
            }
            $response = Configure::response($object, $url);
            return $response;
        } catch (Exception | UrlEmptyException | UrlNotExistException | LocateException $exception){
            return $exception;
        }
    }
}