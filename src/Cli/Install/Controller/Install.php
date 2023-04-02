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
use R3m\Io\Config;

use R3m\Io\Module\Core;
use R3m\Io\Module\Controller;
use R3m\Io\Module\Dir;
use R3m\Io\Module\Event;
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
        $id = $object->config(Config::POSIX_ID);
        $key = App::parameter($object, 'install', 1);
        if(
            !in_array(
                $id,
                [
                    0,
                    33
                ],
                true
            )
        ){
            $exception = new Exception('Only root & www-data can install packages...');
            Event::trigger($object, 'cli.install', [
                'key' => $key,
                'exception' => $exception
            ]);
            throw $exception;
        }
        $url = $object->config('framework.dir.data') .
            $object->config('dictionary.package') .
            $object->config('extension.json')
        ;
        $object->set(Controller::PROPERTY_VIEW_URL, $url);

        $object->config('core.execute.mode', Core::PROMPT);

        $package = $object->parse_select(
            $url,
            'package.' . $key
        );
        if(empty($package)){
            $exception = new Exception('Package: ' . $key . PHP_EOL);
            Event::trigger($object, 'cli.install', [
                'key' => $key,
                'exception' => $exception
            ]);
            throw $exception;
        }
        if($package->has('composer')){
            Dir::change($object->config('project.dir.root'));
            Core::execute($object, $package->get('composer'), $output, $notification);
            if($output){
                echo $output;
            }
            if($notification){
                echo $notification;
            }
        }
        if(
            $package->has('route') &&
            is_array($package->get('route'))
        ){
            foreach($package->get('route') as $route){
                if(File::exist($route)){
                    $command = '{{binary()}} configure route resource "' . $route . '"';
                    $parse = new Parse($object, $object->data());
                    $command = $parse->compile($command, $object->data());
                    Core::execute($object, $command, $output, $error);
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
            $package->has('route') &&
            is_string($package->get('route'))
        ){
            if(File::exist($package->get('route'))){
                $command = '{{binary()}} configure route resource "' . $package->route . '"';
                $parse = new Parse($object, $object->data());
                $command = $parse->compile($command, $object->data());
                Core::execute($object, $command, $output, $error);
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
            $package->has('command') &&
            is_array($package->get('command'))
        ){
            foreach($package->get('command') as $command){
                Core::execute($object, $command, $output, $notification);
                if($output){
                    echo $output;
                }
                if($notification){
                    echo $notification;
                }
            }
        }
        elseif(
            $package->has('command') &&
            is_string($package->get('command'))
        ){
            Core::execute($object, $package->get('command'), $output, $notification);
            if($output){
                echo $output;
            }
            if($notification){
                echo $notification;
            }
        }
        ddd('almost done');
        Event::trigger($object, 'cli.install', [
            'key' => $key,
        ]);
    }
}