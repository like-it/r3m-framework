<?php
/**
 * @author         Remco van der Velde
 * @since         2016-10-19
 * @version        1.0
 * @changeLog
 *     -    all
 */
namespace R3m\Io\Cli\Configure\Controller;

use R3m\io\App;
use R3m\Io\Module\View;
use R3m\io\Module\Dir;

class Configure extends View {
    const DIR = __DIR__;
    const NAME = 'Configure';
    const INFO = 'Info';

    /*
    public static function prerun($object){
        $config = $object->data(App::CONFIG);
        $config->data('controller.dir.source', Configure::DIR);
        $config->data('controller.dir.root', Dir::name(Configure::DIR));
    }
    */

    public static function run($object){
        $module = $object->parameter($object, 'configure', 1);
        if(empty($module)){
            $module = Configure::INFO;
        }
        $action = $object->parameter($object, 'configure', 2);
        if(!empty($action)){
            $url = Configure::locate($object, ucfirst(strtolower($module)) . '.' . strtolower($action));
        } else {
            $url = Configure::locate($object, ucfirst(strtolower($module)));
        }
        return Configure::view($object, $url);
    }
}