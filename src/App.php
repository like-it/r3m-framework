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
namespace R3m\Io;

use R3m\Io\Module\Autoload;
use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\Database;
use R3m\Io\Module\File;
use R3m\Io\Module\FileRequest;
use R3m\Io\Module\Handler;
use R3m\Io\Module\Host;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Response;
use R3m\Io\Module\Route;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

use Exception;
use R3m\Io\Exception\ObjectException;
use R3m\Io\Exception\LocateException;

class App extends Data {
    const NAMESPACE = __NAMESPACE__;
    const NAME = 'App';
    const R3M = 'R3m';

    const SCRIPT = 'script';
    const LINK = 'link';

    const CONTENT_TYPE = 'contentType';
    const CONTENT_TYPE_CSS = 'text/css';
    const CONTENT_TYPE_HTML = 'text/html';
    const CONTENT_TYPE_JSON = 'application/json';
    const CONTENT_TYPE_CLI = 'text/cli';
    const CONTENT_TYPE_FORM = 'application/x-www-form-urlencoded';

    const RESPONSE_JSON = 'json';
    const RESPONSE_HTML = 'html';
    const RESPONSE_FILE = 'file';
    const RESPONSE_OBJECT = 'object';

    const ROUTE = App::NAMESPACE . '.' . Route::NAME;
    const CONFIG = App::NAMESPACE . '.' . Config::NAME;
    const REQUEST = App::NAMESPACE . '.' . Handler::NAME_REQUEST . '.' . Handler::NAME_INPUT;
    const DATABASE = App::NAMESPACE . '.' . Database::NAME;
    const REQUEST_HEADER = App::NAMESPACE . '.' . Handler::NAME_REQUEST . '.' . Handler::NAME_HEADER;

    const AUTOLOAD_COMPOSER = App::NAMESPACE . '.' . 'Autoload' . '.' . 'Composer';
    const AUTOLOAD_R3M = App::NAMESPACE . '.' . 'Autoload' . '.' . App::R3M;

    private Logger $logger;

    public function __construct($autoload, $config){
        $this->data(App::AUTOLOAD_COMPOSER, $autoload);
        $this->data(App::CONFIG, $config);
        App::is_cli();
        require_once 'Debug.php';
        require_once 'Error.php';
    }

    /**
     * @throws Exception
     * @throws ObjectException
     * @throws LocateException
     */
    public static function run(App $object){
        $logger = new Logger('App');
        $logger->pushHandler(new StreamHandler($object->config('project.dir.log') . 'app.log', Logger::DEBUG));
        $uuid = posix_geteuid();
        if(empty($uuid)){
            File::chown($object->config('project.dir.log') . 'app.log', File::USER_WWW, File::USER_WWW);
        }
        $object->logger($logger);
        Core::cors();
        Config::configure($object);
        Handler::request_configure($object);
        if(empty($object->request('request'))){
            $object->logger->info('Logger: App initialized and enabling cors');
        } else {
            $object->logger->info('Logger: App initialized and enabling cors with request: ' . $object->request('request'));
        }
        Host::configure($object);
        Autoload::configure($object);
        Route::configure($object);
        $file = FileRequest::get($object);
        if($file === false){
            try {
                $route = Route::request($object);
                if($route === false){
                    $object->logger()->error('Couldn\'t determine route (' . $object->request('request') .')...');
                    $response = new Response(
                        App::exception_to_json(new Exception(
                            'Couldn\'t determine route (' . $object->request('request') .')...'
                        )),
                        Response::TYPE_JSON,
                        Response::STATUS_ERROR
                    );
                    return Response::output($object, $response);
                } else {
                    if(
                        property_exists($route, 'redirect') &&
                        property_exists($route, 'method') &&
                        in_array(
                            Handler::method(),
                            $route->method
                        )
                    ) {
                        $object->logger->info('Request (' . $object->request('request') .') Redirect: ' . $route->redirect . ' Method: ' . $route->method);
                        Core::redirect($route->redirect);
                    }
                    elseif(
                        property_exists($route, 'redirect') &&
                        !property_exists($route, 'method')
                    ){
                        $object->logger->info('Redirect: ' . $route->redirect);
                        Core::redirect($route->redirect);
                    } else {
                        App::contentType($object);
                        $exception = App::controller($object, $route);
                        if(
                            $exception &&
                            is_object($exception) &&
                            get_class($exception) === 'Exception'
                        ){
                            $object->logger()->error($exception->getMessage());
                            $response = new Response(
                                App::exception_to_json($exception),
                                Response::TYPE_JSON,
                                Response::STATUS_ERROR
                            );
                            return Response::output($object, $response);
                        }
                        $methods = get_class_methods($route->controller);
                        if(empty($methods)){
                            $object->logger()->error('Couldn\'t determine controller (' . $route->controller .') with request (' . $object->request('request') .')');
                            $response = new Response(
                                App::exception_to_json(new Exception(
                            'Couldn\'t determine controller (' . $route->controller .')'
                                )),
                                Response::TYPE_JSON,
                                Response::STATUS_ERROR
                            );
                            return Response::output($object, $response);
                        }
                        if(in_array('controller', $methods)){
                            $object->logger()->info('Function: ' . 'controller' . ' called in controller: ' . $route->controller);
                            $route->controller::controller($object);
                        }
                        if(in_array('configure', $methods)){
                            $object->logger()->info('Function: ' . 'configure' . ' called in controller: ' . $route->controller);
                            $route->controller::configure($object);
                        }
                        if(in_array('before_run', $methods)){
                            $object->logger()->info('Function: ' . 'before_run' . ' called in controller: ' . $route->controller);
                            $route->controller::before_run($object);
                        }
                        if(in_array($route->function, $methods)){
                            $object->logger()->info('Function: ' . $route->function . ' called in controller: ' . $route->controller);
                            $result = $route->controller::{$route->function}($object);
                        } else {
                            $object->logger()->error(
                                'Controller (' .
                                $route->controller .
                                ') function (' .
                                $route->function .
                                ') not exist.'
                            );
                            $response = new Response(
                                App::exception_to_json(new Exception(
                            'Controller (' .
                                    $route->controller .
                                    ') function (' .
                                    $route->function .
                                    ') not exist.'
                                )),
                                Response::TYPE_JSON,
                                Response::STATUS_ERROR
                            );
                            return Response::output($object, $response);
                        }
                        if(in_array('after_run', $methods)){
                            $object->logger()->info('Function: ' . 'after_run' . ' called in controller: ' . $route->controller);
                            $route->controller::after_run($object);
                        }
                        if(in_array('before_result', $methods)){
                            $object->logger()->info('Function: ' . 'before_result' . ' called in controller: ' . $route->controller);
                            $route->controller::before_result($object);
                        }
                        $object->logger()->info('Function: ' . 'result' . ' called in controller: ' . $route->controller);
                        $result = App::result($object, $result);
                        if(in_array('after_result', $methods)){
                            $object->logger()->info('Function: ' . 'after_result' . ' called in controller: ' . $route->controller);
                            $route->controller::after_result($object);
                        }
                        return $result;
                    }
                }
            } catch (Exception $exception) {
                try {
                    if($object->data(App::CONTENT_TYPE) === App::CONTENT_TYPE_JSON){
                        if(!headers_sent()){
                            header('Content-Type: application/json');
                        }
                        $object->logger()->error($exception->getMessage());
                        return App::exception_to_json($exception);
                    }
                } catch (ObjectException $exception){
                    return $exception;
                }
            }
        } else {
            $object->logger()->info('File request: ' . $object->request('request') . ' called...');
            return $file;
        }
    }

    /**
     * @throws Exception
     */
    public static function controller(App $object, $route){
        if(property_exists($route, 'controller')){
            try {
                $check = class_exists($route->controller);
                if(empty($check)){
                    /*
                     * $response = new Response(
                                    App::exception_to_json(new Exception(
                                'Couldn\'t determine controller (' . $route->controller .')'
                                    )),
                                    Response::TYPE_JSON,
                                    Response::STATUS_ERROR
                                );
                                return Response::output($object, $response);
                     */
                    throw new Exception('Cannot call controller (' . $route->controller .')');
                }
            } catch (Exception $exception){
                return $exception;
            }
        } else {
            throw new Exception('Missing controller in route');
        }
    }

    /**
     * @throws Exception
     */
    public static function contentType(App $object){
        $contentType = $object->data(App::CONTENT_TYPE);
        if(empty($contentType)){
            $contentType = App::CONTENT_TYPE_HTML;
            if(property_exists($object->data(App::REQUEST_HEADER), '_')){
                $contentType = App::CONTENT_TYPE_CLI;
            }
            elseif(property_exists($object->data(App::REQUEST_HEADER), 'Content-Type')){
                $contentType = $object->data(App::REQUEST_HEADER)->{'Content-Type'};
            }
            if(empty($contentType)){
                throw new Exception('Couldn\'t determine contentType');
            }
            return $object->data(App::CONTENT_TYPE, $contentType);
        } else {
            return $contentType;
        }
    }

    private static function exception_to_json(Exception $exception){
        $class = get_class($exception);
        $array = [];
        $array['class'] = $class;
        $array['message'] = $exception->getMessage();
        if(stristr($class, 'locateException') !== false){
            $array = $exception->toArray($array);
        }
        $array['line'] = $exception->getLine();
        $array['file'] = $exception->getFile();
        $array['code'] = $exception->getCode();
        $array['previous'] = $exception->getPrevious();
        $array['trace'] = $exception->getTrace();
        $array['trace_as_string'] = $exception->getTraceAsString();
        try {
            return Core::object($array, Core::OBJECT_JSON);
        } catch (ObjectException $exception) {
            return $exception;
        }
    }

    public static function response_output(App $object, $output=App::CONTENT_TYPE_HTML){
        $object->config('response.output', $output);
    }

    private static function result(App $object, $output){
        if($output instanceof Exception){
            if(App::is_cli()){
                return App::exception_to_json($output);
            } else {
                if(!headers_sent()){
                    header('Content-Type: application/json');
                }
                return App::exception_to_json($output);
            }
        }
        elseif($output instanceof Response){
            return Response::output($object, $output);
        } else {
            $response = new Response($output, $object->config('response.output'));
            return Response::output($object, $response);
        }
    }

    public function logger($logger=null){
        if($logger !== null){
            $this->setLogger($logger);
        }
        return $this->getLogger();
    }

    private function setLogger($logger=null){
        $this->logger = $logger;
    }

    private function getLogger(){
        return $this->logger;
    }

    public function route(){
        return $this->data(App::ROUTE);
    }

    public function config($attribute=null, $value=null){
        return $this->data(App::CONFIG)->data($attribute, $value);
    }

    public function request($attribute=null, $value=null){                
        return $this->data(App::REQUEST)->data($attribute, $value);        
    }

    public static function parameter($object, $parameter='', $offset=0){
        return parent::parameter($object->data(App::REQUEST)->data(), $parameter, $offset);
    }

    public function session($attribute=null, $value=null){
        return Handler::session($attribute, $value);
    }

    public function cookie($attribute=null, $value=null, $duration=null){
        return Handler::cookie($attribute, $value, $duration);
    }

    public function upload($number=null){
        if($number === null){
            return new Data($this->data(
                App::NAMESPACE . '.' .
                Handler::NAME_REQUEST . '.' .
                Handler::NAME_FILE
            ));
        } else {
            return new Data($this->data(
                App::NAMESPACE . '.' .
                Handler::NAME_REQUEST . '.' .
                Handler::NAME_FILE . '.' .
                $number
            ));
        }
    }

    public function data_read($url, $attribute=null, $do_not_nest_key=false){
        if($attribute !== null){
            $data = $this->data($attribute);
            if(!empty($data)){
                return $data;
            }
        }
        if(File::exist($url)){
            $read = File::read($url);
            if($read){
                $data = new Data();
                $data->do_not_nest_key($do_not_nest_key);
                $data->data(Core::object($read, Core::OBJECT_OBJECT))   ;
            } else {
                $data = new Data();
                $data->do_not_nest_key($do_not_nest_key);
            }
            if($attribute !== null){
                $this->data($attribute, $data);
            }
            return $data;
        } else {
            return false;
        }
    }

    public function parse_read($url, $attribute=null){
        if($attribute !== null){
            $data = $this->data($attribute);
            if(!empty($data)){
                return $data;
            }
        }
        if(File::exist($url)){
            $read = File::read($url);
            if($read){
                $mtime = File::mtime($url);
                $parse = new Parse($this);
                $parse->storage()->data('r3m.io.parse.view.url', $url);
                $parse->storage()->data('r3m.io.parse.view.mtime', $mtime);
                $this->data('ldelim', '{');
                $this->data('rdelim', '}');
                $data = clone $this->data();
                unset($data->{App::NAMESPACE});
                $read = $parse->compile(Core::object($read), $data, $parse->storage());
                $data = new Data($read);
                $script = Parse::readback($this, $parse, App::SCRIPT);
                if(!empty($script)){
                    $script_old = $data->data('script');
                    if(empty($script_old)){
                        $script_old = [];
                    }
                    $script = array_merge($script_old, $script);
                    $data->data('script', $script);
                }
                $link = Parse::readback($this, $parse, App::LINK);
                if(!empty($link)){
                    $link_old = $data->data('link');
                    if(empty($link_old)){
                        $link_old = [];
                    }
                    $link = array_merge($link_old, $link);
                    $data->data('link', $link);
                }
            } else {
                $data = new Data();
            }
            if($attribute !== null){
                $this->data($attribute, $data);
            }
            return $data;
        } else {
            return false;
        }
    }

    public static function is_cli(){
        if(!defined('IS_CLI')){
            return Core::is_cli();
        } else {
            return true;
        }
    }
}
