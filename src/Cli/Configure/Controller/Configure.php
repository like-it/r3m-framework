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
use R3m\io\App;
use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;
use R3m\Io\Module\View;

class Configure extends View {
    const DIR = __DIR__;
    const NAME = 'Configure';
    const INFO = 'Info';

    public static function run(App $object){
        $module = $object->parameter($object, 'configure', 1);
        if(empty($module)){
            $module = Configure::INFO;
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
            return 'Action undefined.' . "\n";
        }

    }
}