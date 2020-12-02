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
namespace R3m\Io\Cli\Info\Controller;

use Exception;
use R3m\Io\App;
use R3m\Io\Config;
use R3m\Io\Module\View;

class Info extends View {
    const DIR = __DIR__;
    const NAME = 'Info';

    public static function run($object){
        $config = $object->data(App::NAMESPACE . '.' . Config::NAME);        
        $command = $object::parameter($object, Info::NAME, 1);
        $url = Info::locate($object, 'Info\\' . $command);
        if(empty($url)){            
            $url = Info::locate($object, 'Info');

        }
        return Info::view($object, $url);
    }
}