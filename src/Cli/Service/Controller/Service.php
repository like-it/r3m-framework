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
namespace R3m\Io\Cli\Service\Controller;

use Exception;
use R3m\Io\App;
use R3m\Io\Config;
use R3m\Io\Module\File;
use R3m\Io\Module\Dir;
use R3m\Io\Module\View;

class Service extends View {
    const DIR = __DIR__;
    const NAME = 'Service';

    const TARGET = '/usr/bin/';
    const EXE = 'R3m.php';

    public static function run($object){
        $command = $object->parameter($object, Service::NAME, 1);
        $method = $object->parameter($object, Service::NAME, 2);

        switch($command){
            case 'cron' :
                $class = 'R3m\\Io\\Module\\' . $command;

                if(empty($method)){
                    $method = $class::DEFAULT_COMMAND;
                }
                if(!method_exists($class, $method)){
                    throw new Exception('Method not exist in ' . $class);
                }
                return $class::{$method}($object);
            break;
            default:
                $url = Service::locate($object, 'Info');
                return Service::view($object, $url);
        }
    }
}