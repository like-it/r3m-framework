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
    const NAME = 'Uuid';

    /*
    public static function prerun($object){
        $config = $object->data(App::CONFIG);
        $config->data('controller.dir.source', Configure::DIR);
        $config->data('controller.dir.root', Dir::name(Configure::DIR));
    }
    */

    public static function run($object){
        $action = $object->parameter($object, 'configure', 1);
        switch(strtolower($action)){
            case 'host' :
                $url = Configure::locate($object, 'Host');
                return Configure::view($object, $url);
            break;

        }
        $url = Configure::locate($object, 'Info');
        return Configure::view($object, $url);
    }
}