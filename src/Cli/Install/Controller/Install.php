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
                $command = '{{binary()}} configure route resource "' . $package->get('route') . '"';
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
            $package->has('copy') &&
            is_array($package->get('copy'))
        ){
            foreach($package->get('copy') as $copy){
                if(
                    property_exists($copy, 'from') &&
                    property_exists($copy, 'to') &&
                    property_exists($copy, 'recursive') &&
                    $copy->recursive === true
                ){
                    $parse = new Parse($object, $object->data());
                    $copy->to = $parse->compile($copy->to, $object->data());
                    if(File::exist($copy->from)){
                        if(Dir::is($copy->from)){
                            Dir::create($copy->to, Dir::CHMOD);
                            if($object->config(Config::POSIX_ID) === 0){
                                $command = 'chown www-data:www-data ' . $copy->to;
                                exec($command);
                                $dir_package = Dir::name($copy->to);
                                $command = 'chown www-data:www-data ' . $dir_package;
                                exec($command);
                            }
                            if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                                $command = 'chmod 777 ' . $copy->to;
                                exec($command);
                                $dir_package = Dir::name($copy->to);
                                $command = 'chmod 777 ' . $dir_package;
                                exec($command);
                            }
                            $dir = new Dir();
                            $read = $dir->read($copy->from, true);
                            foreach($read as $file){
                                if($file->type === Dir::TYPE){
                                    $create = str_replace($copy->from, $copy->to, $file->url);
                                    Dir::create($create, Dir::CHMOD);
                                    if($object->config(Config::POSIX_ID) === 0){
                                        $command = 'chown www-data:www-data ' . $create;
                                        exec($command);
                                    }
                                    if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                                        $command = 'chmod 777 ' . $create;
                                        exec($command);
                                    }
                                }
                            }
                            foreach($read as $file){
                                if($file->type === File::TYPE){
                                    $to = str_replace($copy->from, $copy->to, $file->url);
                                    if(!File::exist($to)){
                                        File::copy($file->url, $to);
                                        if($object->config(Config::POSIX_ID) === 0){
                                            $command = 'chown www-data:www-data ' . $to;
                                            exec($command);
                                        }
                                        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                                            $command = 'chmod 666 ' . $to;
                                            exec($command);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                elseif(
                    property_exists($copy, 'from') &&
                    property_exists($copy, 'to')
                ){
                    $parse = new Parse($object, $object->data());
                    $copy->to = $parse->compile($copy->to, $object->data());
                    if(File::exist($copy->from)){
                        if(Dir::is($copy->from)){
                            Dir::create($copy->to, Dir::CHMOD);
                            if($object->config(Config::POSIX_ID) === 0){
                                $command = 'chown www-data:www-data ' . $copy->to;
                                exec($command);
                                $dir_package = Dir::name($copy->to);
                                $command = 'chown www-data:www-data ' . $dir_package;
                                exec($command);
                            }
                            if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                                $command = 'chmod 777 ' . $copy->to;
                                exec($command);
                                $dir_package = Dir::name($copy->to);
                                $command = 'chmod 777 ' . $dir_package;
                                exec($command);
                            }
                            $dir = new Dir();
                            $read = $dir->read($copy->from, true);
                            foreach($read as $file){
                                if($file->type === Dir::TYPE){
                                    Dir::create($file->url, Dir::CHMOD);
                                    if($object->config(Config::POSIX_ID) === 0){
                                        $command = 'chown www-data:www-data ' . $create;
                                        exec($command);
                                    }
                                    if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                                        $command = 'chmod 777 ' . $create;
                                        exec($command);
                                    }
                                }
                            }
                            foreach($read as $file){
                                if($file->type === File::TYPE){
                                    $to = str_replace($copy->from, $copy->to, $file->url);
                                    if(!File::exist($to)){
                                        File::copy($file->url, $to);
                                        if($object->config(Config::POSIX_ID) === 0){
                                            $command = 'chown www-data:www-data ' . $to;
                                            exec($command);
                                        }
                                        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                                            $command = 'chmod 666 ' . $to;
                                            exec($command);
                                        }
                                    }

                                }
                            }
                        }
                    }
                }
            }
        }
        $command = '{{binary()}} cache:clear';
        $parse = new Parse($object, $object->data());
        $command = $parse->compile($command, $object->data());
        Core::execute($object, $command, $output);
        if($output){
            echo $output;
        }
        echo 'Press ctrl-c to stop the installation...' . PHP_EOL;
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
        Event::trigger($object, 'cli.install', [
            'key' => $key,
        ]);
    }
}