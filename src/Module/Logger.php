<?php
/**
 * @author          Remco van der Velde
 * @since           13-03-2022
 * @copyright       (c) Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *  -    all
 */
namespace R3m\Io\Module;

use R3m\Io\App;
use R3m\Io\Config;

use Exception;

class Logger {

    /**
     * @throws Exception
     */
    public static function configure(App $object){
        $interface = $object->config('log');
        $is = null;
        if($interface){
            foreach($interface as $name => $record){
                $name = ucfirst($name);
                if(
                    property_exists($record, 'default') &&
                    !empty($record->default)
                ){
                    $object->config('project.log.name', $name);
                }
                if(
                    property_exists($record, 'is') &&
                    !empty($record->is)
                ){
                    $is = $record->is;
                }
                if(
                    property_exists($record, 'class') &&
                    !empty($record->class) &&
                    is_string($record->class)
                ){
                    if(
                        property_exists($record, 'parameters') &&
                        !empty($record->parameters) &&
                        is_array($record->parameters)
                    ){
                        //use constants in config & replace them here
                        $parameters = $record->parameters;
                        $parameters = Config::parameters($object, $parameters);
                    } else {
                        $parameters = [];
                        $parameters[] = $name;
                    }
                    $logger = new $record->class(...$parameters);
                    if(
                        property_exists($record, 'handler') &&
                        !empty($record->handler) &&
                        is_array($record->handler)
                    ){
                        foreach($record->handler as $handler){
                            if(
                                property_exists($handler, 'class') &&
                                !empty($handler->class) &&
                                is_string($handler->class)
                            ){
                                if(
                                    property_exists($handler, 'parameters') &&
                                    !empty($handler->parameters) &&
                                    is_array($handler->parameters)
                                ){
                                    //use constants in config & replace them here
                                    $parameters = $handler->parameters;
                                    $parameters = Config::parameters($object, $parameters);
                                } else {
                                    $parameters = [];
                                }
                                $push = new $handler->class(...$parameters);
                                if(
                                    property_exists($handler, 'formatter') &&
                                    !empty($handler->formatter) &&
                                    is_object($handler->formatter)
                                ){
                                    if(
                                        property_exists($handler->formatter, 'class') &&
                                        !empty($handler->formatter->class) &&
                                        is_string($handler->formatter->class)
                                    ){
                                        if(
                                            property_exists($handler->formatter, 'parameters') &&
                                            !empty($handler->formatter->parameters) &&
                                            is_array($handler->formatter->parameters)
                                        ){
                                            //use constants in config & replace them here
                                            $parameters = $handler->formatter->parameters;
                                            $parameters = Config::parameters($object, $parameters);
                                        } else {
                                            $parameters = [];
                                        }
                                        if(method_exists($push, 'setFormatter')){
                                            $formatter = new $handler->formatter->class(...$parameters);
                                            $push->setFormatter($formatter);
                                        }
                                    }
                                }
                                elseif(
                                    !property_exists($handler, 'formatter') &&
                                    method_exists($push, 'setFormatter')
                                ){
                                    $formatter =new \Monolog\Formatter\LineFormatter();
                                    $push->setFormatter($formatter);
                                }
                                if(method_exists($logger, 'pushHandler')){
                                    $logger->pushHandler($push);
                                }
                            }
                        }
                    }
                    if(
                        property_exists($record, 'processor') &&
                        !empty($record->processor) &&
                        is_array($record->processor)
                    ){
                        foreach($record->processor as $processor){
                            if(
                                property_exists($processor, 'class') &&
                                !empty($processor->class) &&
                                is_string($processor->class)
                            ){
                                if(
                                    property_exists($processor, 'parameters') &&
                                    !empty($processor->parameters) &&
                                    is_array($processor->parameters)
                                ){
                                    //use constants in config & replace them here
                                    $parameters = $processor->parameters;
                                    $parameters = Config::parameters($object, $parameters);
                                } else {
                                    $parameters = [];
                                }
                                $push = new $processor->class(...$parameters);
                                if(method_exists($logger, 'pushProcessor')){
                                    $logger->pushProcessor($push);
                                }
                            }
                        }
                    }
                    $logName = lcfirst($logger->getName());
                    $object->logger($logger->getName(), $logger);
                    if($logName !== 'name'){
                        $object->config('project.log.' . $logName, $logger->getName());
                    }
                    if(
                        property_exists($record, 'channel') &&
                        !empty($record->channel) &&
                        is_array($record->channel)
                    ){
                        foreach($record->channel as $withName){
                            $withName = ucfirst($withName);
                            $channel = $logger->withName($withName);
                            $logName = lcfirst($withName);
                            if($logName !== 'name'){
                                $object->config('project.log.' . $logName, $withName);
                            }
                            $object->logger($channel->getName(), $channel);
                            $object->logger($channel->getName())->info('Channel initialised.', [$withName]);
                        }
                    }
                }
            }
        }
        $uuid = posix_geteuid();

        $is_chown =false;
        if(
            $is &&
            is_object($is) &&
            property_exists($is, 'chown')){
            $is_chown = $is->chown;
        }
        if(empty($uuid) && $is_chown === false){
            $dir = $object->config('project.dir.log');
            $command = 'chown www-data:www-data ' . $dir . ' -R';
            Core::execute($object, $command);
        }
    }

    /**
     * @throws Exception
     */
    public static function alert($message=null, $context=[], $channel=''){
        $object = App::instance();
        if(empty($channel)){
            $channel = $object->config('project.log.name');
        } else {
            $channel = ucfirst($channel);
        }
        if($channel){
            $object->logger($channel)->alert($message, $context);
        }

    }

    /**
     * @throws Exception
     */
    public static function critical($message=null, $context=[], $channel=''){
        $object = App::instance();
        if(empty($channel)){
            $channel = $object->config('project.log.error');
        } else {
            $channel = ucfirst($channel);
        }
        if($channel){
            $object->logger($channel)->critical($message, $context);
        }

    }

    /**
     * @throws Exception
     */
    public static function debug($message=null, $context=[], $channel=''){
        $object = App::instance();
        if(empty($channel)){
            $channel = $object->config('project.log.name');
        } else {
            $channel = ucfirst($channel);
        }
        if($channel){
            $object->logger($channel)->debug($message, $context);
        }
    }

    /**
     * @throws Exception
     */
    public static function emergency($message=null, $context=[], $channel=''){
        $object = App::instance();
        if(empty($channel)){
            $channel = $object->config('project.log.error');
        } else {
            $channel = ucfirst($channel);
        }
        if($channel){
            $object->logger($channel)->emergency($message, $context);
        }
    }

    /**
     * @throws Exception
     */
    public static function error($message=null, $context=[], $channel=''){
        $object = App::instance();
        if(empty($name)){
            $channel = $object->config('project.log.error');
        } else {
            $channel = ucfirst($channel);
        }
        if($channel){
            $object->logger($channel)->error($message, $context);
        }
    }

    /**
     * @throws Exception
     */
    public static function info($message=null, $context=[], $channel=''){
        $object = App::instance();
        if(empty($channel)){
            $channel = $object->config('project.log.name');
        } else {
            $channel = ucfirst($channel);
        }
        if($channel){
            $object->logger($channel)->info($message, $context);
        }
    }

    /**
     * @throws Exception
     */
    public static function notice($message=null, $context=[], $channel=''){
        $object = App::instance();
        if(empty($channel)){
            $channel = $object->config('project.log.name');
        } else {
            $channel = ucfirst($channel);
        }
        if($channel){
            $object->logger($channel)->notice($message, $context);
        }
    }

    /**
     * @throws Exception
     */
    public static function warning($message=null, $context=[], $channel=''){
        $object = App::instance();
        if(empty($channel)){
            $channel = $object->config('project.log.name');
        } else {
            $channel = ucfirst($channel);
        }
        if($channel){
            $object->logger($channel)->warning($message, $context);
        }
    }
}