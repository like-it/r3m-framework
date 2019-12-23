<?php
/**
 * @author         Remco van der Velde
 * @since         2016-10-19
 * @version        1.0
 * @changeLog
 *     -    all
 */
namespace R3m\Io\Cli;

use Exception;
use R3m\Io\App;
use R3m\Io\Config;
use R3m\Io\Module\View;

class Info extends View {
    const DIR = __DIR__;

    public static function run($object){
        $debug = debug_backtrace(true);
        $config = $object->data(App::NAMESPACE . '.' . Config::NAME);
        $config->data('framework.environment', Config::MODE_PRODUCTION);
        $action = $object::parameter($object, 'info', 1);
        $url = Info::locate($object, 'Info\\' . $action);
        if(empty($url)){
//             $config->data('framework.environment', Config::MODE_DEVELOPMENT);
            $url = Info::locate($object, 'Info');

        }
        $main = Info::view($object, $url);
        return $main;
    }
}