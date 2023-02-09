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
namespace R3m\Io\Cli\Install\Controller;

use R3m\Io\App;
use R3m\Io\Module\Core;
use R3m\Io\Module\Controller;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;
use R3m\Io\Module\Parse;

use Exception;

use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;
use R3m\Io\Exception\RouteExistException;

class Install extends Controller {
    const DIR = __DIR__;
    const NAME = 'Install';
    const INFO = '{{binary()}} install                        | Install packages';

    /**
     * @throws Exception
     */
    public static function run(App $object){
        $package = App::parameter($object, 'install', 1);
        $url = $object->config('framework.dir.data') . $object->config('dictionary.package') . $object->config('extension.json');
        $data = new Data($object->data());
        $parse = new Parse($object, $data);
        $package = Core::object_select($parse, $data, $url, 'package.' . $package);

        ddd($package);
        ddd($url);


        switch($package){
            case 'r3m-io/priya' :
                $command = 'composer require ' . $package;
                Core::execute($command, $output, $error);
                if($output){
                    echo $output;
                }
                if($error){
                    echo $error;
                }
                $url =
                    $object->config('project.dir.vendor') .
                    'r3m-io' .
                    $object->config('ds') .
                    'priya' .
                    $object->config('ds') .
                    $object->config('dictionary.data') .
                    $object->config('ds') .
                    'Route' .
                    $object->config('extension.json');
                if(File::exist($url)){
                    $command = '{{binary()}} configure route resource "' . $url . '"';
                    $parse = new Parse($object, $object->data());
                    $command = $parse->compile($command, $object->data());
                    Core::execute($command, $output, $error);
                    if($output){
                        echo $output;
                    }
                    if($error){
                        if(stristr($error, RouteExistException::MESSAGE) === false) {
                            echo $error;
                        }
                    }
                    //execute '{{binary()}} r3m-io/priya setup "{{config('project.dir.data') + 'Package' + config('ds') + 'r3m-io/priya' + '.installation' + config('extension.json')}}"'
                }
            break;
            default:
                throw new Exception('Cannot install package: ' . $package);
        }
    }
}