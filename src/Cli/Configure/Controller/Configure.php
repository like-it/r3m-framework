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

use Exception;
use R3m\Io\App;
use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;
use R3m\Io\Module\View;

class Configure extends View {
    const DIR = __DIR__;
    const NAME = 'Configure';
    const MODULE_INFO = 'Info';
    const INFO = '{binary()} configure                      | App configuration commands';
    const INFO_RUN = [
        '{binary()} configure                      | App configuration commands',
        '{binary()} configure domain add           | Adds a domain to /project_dir/Host',
        '{binary()} configure environment toggle   | Toggle environment between development, staging & production',
        '{binary()} configure host add             | Adds a host to /etc/host',
        '{binary()} configure host create          | Create and setup an apache2 site',
        '{binary()} configure host delete          | Delete a host from /etc/host',
        '{binary()} configure public create        | Creates the public html directory',
        '{binary()} configure route resource       | Add a main route based on resource',
        '{binary()} configure route delete         | Delete a main route based on resource',
        '{binary()} configure server admin         | Set the server admin',
        '{binary()} configure site create          | Create an apache2 site file',
        '{binary()} configure site delete          | Delete an apache2 site file',
        '{binary()} configure site disable         | Disable an apache2 site',
        '{binary()} configure site enable          | Enable an apache2 site'
    ];

    public static function run(App $object){
        $module = $object->parameter($object, 'configure', 1);
        if(empty($module)){
            $module = Configure::MODULE_INFO;
        }
        $action = $object->parameter($object, 'configure', 2);
        try {
            if(!empty($action)){
                $url = Configure::locate($object, ucfirst(strtolower($module)) . '.' . ucfirst(strtolower($action)));
            } else {
                $url = Configure::locate($object, ucfirst(strtolower($module)));
            }
            return Configure::response($object, $url);
        } catch (Exception | UrlEmptyException | UrlNotExistException | LocateException $exception){
            d($exception);
            return 'Action undefined.' . PHP_EOL;
        }

    }
}