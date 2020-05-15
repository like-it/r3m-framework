<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *  -    all
 */

namespace R3m\Io\Module;

use stdClass;
use R3m\Io\App;


class Cron extends View {
    const DIR = __DIR__;
    const NAME = 'Cron';
    const DEFAULT_COMMAND = 'info';

    const LOCK = 'Cron/.Lock';

    public static function info(App $object){
        $config = $object->data(App::CONFIG);
        $url = $config->data('framework.dir.data') . 'Cron.json';
        $read = Core::object(File::read($url));
        $parse = new Parse($object);
        $data = new Data();
        $object->data('r3m.config', $config->data());

//         dd($object->data('r3m.config'));

        $object->data($parse->compile($read, $object->data()));
        $url = Cron::locate($object, 'Cron/Info');
        return Cron::view($object, $url);
    }

    public static function start(App $object){
        $config = $object->data(App::CONFIG);
        $url = $config->data('framework.dir.data') . 'Cron.json';
        $read = Core::object(File::read($url));
        $parse = new Parse($object);
        $data = new Data();
        $object->data('r3m.config', $config->data());
        $object->data($parse->compile($read, $object->data()));

        if(Cron::lock($object, 'has') === false){
//             d('yep1');
            Cron::service($object);
//             d('yepo2');
        } else {
            $class = __CLASS__;
            $object->data('command', ucfirst(__FUNCTION__));
            $url = Cron::lock($object, 'url');
            $object->data('pid', File::read($url));
            $url = $class::locate($object, 'Cron/Busy');
            return $class::view($object, $url);
        }
    }

    public static function lock(App $object, $type=null){
        $lock =  $object->data('cron.lock') !== null ? $object->data('cron.lock') : $object->data('r3m.config.controller.dir.data') . Cron::LOCK;
        if($type == 'url'){
            return $lock;
        }
        if(File::Exist($lock)){
            if($type == 'has'){
                return true;
            }
            $class = __CLASS__;
            $object->data('command', ucfirst(__FUNCTION__));
            $url = Cron::lock($object, 'url');
            $object->data('pid', File::read($url));

            $url = $class::locate($object, 'Cron/Busy');
            echo $class::view($object, $url);
            die;
        }
        elseif($type !== 'has'){
            $pid = getmypid();
            $read = File::read($lock);
            if(empty($read)){
                $write = File::Write($lock, $pid);
            } else {
                $write = File::Write($lock, $read . ';' . $pid);
            }
            return true;
        } else {
            return false;
        }
    }

    public static function task(App $object){
        $list = [];
        $read = '';

        if(is_array($object->data('cron.dir'))){
            foreach($object->data('cron.dir') as $file){
                $read .= File::read($file->url);
            }
        }
        $parse = new Parse($object);
//         $data = new Data();
        $read = $parse->compile($read, $object->data());

        $explode = explode(PHP_EOL, $read);

        foreach($explode as $nr => $line){
            $line = trim($line);
            if(empty($line)){
                continue;
            }
            $record = new stdClass();
            $line = explode('#', $line, 2);
            $temp = explode(' ', $line[0], 6);
            if(isset($temp[5])){
                $record->minute = $temp[0];
                $record->hour = $temp[1];
                $record->day_of_the_month = $temp[2];
                $record->day_of_the_week = $temp[4];
                $record->month = $temp[3];
                $record->binary = $temp[5];
            }
            if(!Core::object_is_empty($record)){
                $list[] = $record;
            }
        }
        $object->data('cron.task', $list);
    }

    public static function now_attribute($record, $date, $attribute=null){
        if(
            isset($record->$attribute) &&
            isset($date->$attribute)
            ){
                if(
                    !in_array(
                        $record->$attribute,
                        [
                            '*',
                            $date->$attribute
                        ]
                        )
                    ){
                        return false;
                } else {
                    return true;
                }
        }
        return false;
    }

    public static function now($record, $date){
        if(Cron::now_attribute($record, $date, 'minute') === false){
            return false;
        }
        if(Cron::now_attribute($record, $date, 'hour') === false){
            return false;
        }
        if(Cron::now_attribute($record, $date, 'day_of_the_month') === false){
            return false;
        }
        if(Cron::now_attribute($record, $date, 'month') === false){
            return false;
        }
        if(Cron::now_attribute($record, $date, 'day_of_the_week') === false){
            return false;
        }
        return true;
    }

    public static function schedule(App $object){
        $config = $object->data(App::CONFIG);
        $url = $config->data('framework.dir.data') . 'Cron.json';
        $read = Core::object(File::read($url));
        $parse = new Parse($object);
        $data = new Data();
        $object->data('r3m.config', $config->data());
        $object->data($parse->compile($read, $object->data()));
        Core::interactive();
        Cron::Lock($object, 'create');
        Cron::task($object);
        while(true){
            if(Cron::lock($object, 'has') === false){
                return;
            }
            $task = $object->data('cron.task');
            $time_current = microtime(true);
            $date = date('Y-m-d H:i:00', $time_current);
            $time_next = strtotime($date) + 60;
            $sleep = $time_next - $time_current;
            $usleep = $sleep * 1000000;
            usleep($usleep);
            echo 'Cron ticker: '. $date . PHP_EOL;
            $active = 0;
            $current = new stdClass();
            $current->minute = date('i', $time_next);
            $current->hour = date('H', $time_next);
            $current->day_of_the_month = date('j', $time_next);
            $current->month = date('n', $time_next);
            $current->day_of_the_week  = date('w', $time_next);
            if(
                $task !== null &&
                is_array($task)
                ){
                    foreach($task as $record){
                        if(Cron::now($record, $current) === false){
                            continue;
                        }
                        $active++;
                        Core::async($record->binary);
//                         $output = [];
//                         Core::execute($record->binary, $output);
                    }
                    if($active > 0){
                        $object->data('cron.active', $active);
                        //use template Active.tpl
                        //active to variable cron.active
                        echo 'Cron jobs tasks activated (' . $active .') at: '. $date . PHP_EOL;
                    }
            }
        }
    }


    public static function stop(App $object){
        $config = $object->data(App::CONFIG);
        $url = $config->data('framework.dir.data') . 'Cron.json';
        $read = Core::object(File::read($url));
        $parse = new Parse($object);
        $data = new Data();
        $object->data('r3m.config', $config->data());
        $object->data($parse->compile($read, $object->data()));
        $lock =  $object->data('cron.lock') !== null ? $object->data('cron.lock') : $object->data('r3m.config.controller.dir.data') . Cron::LOCK;
        $read = File::read($lock);
        $explode = explode(';', $read);
        if(File::exist($lock)){
            File::delete($lock);
        }
        foreach($explode as $pid){
            if(empty($pid)){
                continue;
            }
            $output = [];
            $exec = 'ps -p ' . $pid;
            Core::execute($exec, $output);
            if(array_key_exists(1, $explode)){
                $execute = 'kill '  . $pid;
                Core::async($execute);
            } else {
                $object->data('error', 'no.running.process');
            }
        }
        $class = __CLASS__;
        $object->data('command', ucfirst(__FUNCTION__));
        $url = $class::locate($object, 'Cron/' . $object->data('command'));
        return $class::view($object, $url);
    }

    public static function service(App $object){
        //init schedule as process...
        $command = Core::binary() . ' service cron schedule';
        Core::async($command); //do async
        return;
    }
}