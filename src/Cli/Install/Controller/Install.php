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
        $item = App::parameter($object, 'install', 1);
        $url = $object->config('framework.dir.data') . $object->config('dictionary.package') . $object->config('extension.json');

        $data = new Data($object->data());
        $data->data('r3m.io.parse.view.url', $url);
        $parse = new Parse($object);
        $package = Core::object_select(
            $parse,
            $data,
            $url,
            'package.' . $item,
            true
        );
        ddd($package);
        if(empty($package)){
            throw new Exception('Package: ' . $item . PHP_EOL);
        }
        if(property_exists($package, 'composer')){
            Core::execute($package->composer, $output, $error);
            if($output){
                echo $output;
            }
        }
        if(
            property_exists($package, 'route') &&
            is_array($package->route)
        ){
            foreach($package->route as $route){
                if(File::exist($route)){
                    $command = '{{binary()}} configure route resource "' . $route . '"';
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
                }
            }
        }
        elseif(
            property_exists($package, 'route') &&
            is_string($package->route)
        ){
            if(File::exist($package->route)){
                $command = '{{binary()}} configure route resource "' . $package->route . '"';
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
            }
        }
        if(
            property_exists($package, 'command') &&
            is_array($package->command)
        ){
            foreach($package->command as $command){
                Core::execute($command, $output, $error);
                if($output){
                    echo $output;
                }
                if($error){
                    echo $error;
                }
            }
        }
        elseif(
            property_exists($package, 'command') &&
            is_string($package->command)
        ){
            Core::execute($package->command, $output, $error);
            if($output){
                echo $output;
            }
            if($error){
                echo $error;
            }
        }
    }
}