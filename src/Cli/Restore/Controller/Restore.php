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

class Restore extends View {
    const DIR = __DIR__;
    const NAME = 'Restore';
    const DEFAULT_INFO = 'Info';
    const INFO = '{{binary()}} restore                        | App restore commands';
    const INFO_RUN = [
        '{{binary()}} restore                        | App restore files',
        '{{binary()}} restore index.php              | Restores /Application/Public/index.php',
        '{{binary()}} restore .htaccess              | Restores /Application/Public/.htaccess',
    ];

    public static function run(App $object){
        $file = $object->parameter($object, 'restore', 1);
        if(empty($file)){
            $file = Restore::DEFAULT_INFO;
        }
        d($object->config('framework.dir.src'));
        dd($file);
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