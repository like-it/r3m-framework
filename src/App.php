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

use R3m\Io\Exception\AuthorizationException;
use R3m\Io\Module\Autoload;
use R3m\Io\Module\Cli;
use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\Database;
use R3m\Io\Module\Dir;
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
use R3m\Io\Exception\FileWriteException;
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

    const LOGGER_NAME = 'App';

    const ROUTE = App::NAMESPACE . '.' . Route::NAME;
    const CONFIG = App::NAMESPACE . '.' . Config::NAME;
    const REQUEST = App::NAMESPACE . '.' . Handler::NAME_REQUEST . '.' . Handler::NAME_INPUT;
    const DATABASE = App::NAMESPACE . '.' . Database::NAME;
    const REQUEST_HEADER = App::NAMESPACE . '.' . Handler::NAME_REQUEST . '.' . Handler::NAME_HEADER;

    const AUTOLOAD_COMPOSER = App::NAMESPACE . '.' . 'Autoload' . '.' . 'Composer';
    const AUTOLOAD_R3M = App::NAMESPACE . '.' . 'Autoload' . '.' . App::R3M;

    private $logger = [];

    /**
     * @throws Exception
     */
    public function __construct($autoload, $config){
        $this->data(App::AUTOLOAD_COMPOSER, $autoload);
        $this->data(App::CONFIG, $config);
        App::is_cli();
        require_once 'Debug.php';
        require_once 'Error.php';
        $logger = new Logger(App::LOGGER_NAME);
        $logger->pushHandler(new StreamHandler($this->config('project.dir.log') . 'app.log', Logger::DEBUG));
        $uuid = posix_geteuid();
        if(empty($uuid)){
            $url = $this->config('project.dir.log') . 'app.log';
            if(File::exist($url)) {
                File::chown($url, File::USER_WWW, File::USER_WWW);
            }
            $url = $this->config('project.dir.log') . 'access.log';
            if(File::exist($url)){
                File::chown($url, File::USER_WWW, File::USER_WWW);
            }
            $url = $this->config('project.dir.log') . 'error.log';
            if(File::exist($url)){
                File::chown($url, File::USER_WWW, File::USER_WWW);
            }
        }
        $this->logger($logger->getName(), $logger);
        Config::configure($this);
        Autoload::configure($this);
    }

    /**
     * @throws Exception
     * @throws ObjectException
     * @throws LocateException
     */
    public static function run(App $object){
        if(App::is_cli() === false){
            Core::cors($object);
        }
        //Config::configure($object); //@moved to construct
        Handler::request_configure($object);
        if(empty($object->request('request'))){
            $object->logger(App::LOGGER_NAME)->info('Logger: App initialized and enabling cors');
        } else {
            $object->logger(App::LOGGER_NAME)->info('Logger: App initialized and enabling cors with request: ' . $object->request('request'));
        }
        Host::configure($object);
        //Autoload::configure($object); //@moved to construct
        Route::configure($object);
        $file = FileRequest::get($object);
        if($file === false){
            try {
                $route = Route::request($object);
                if($route === false){
                    if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                        $object->logger(App::LOGGER_NAME)->error('Couldn\'t determine route (' . $object->request('request') .')...');
                        $response = new Response(
                            App::exception_to_json(new Exception(
                                'Couldn\'t determine route (' . $object->request('request') .')...'
                            )),
                            Response::TYPE_JSON,
                            Response::STATUS_ERROR
                        );
                        return Response::output($object, $response);
                    } else {
                        $route = Route::wildcard($object);
                        if($route === false){
                            $object->logger(App::LOGGER_NAME)->error('Couldn\'t determine route (wildcard) (' . $object->request('request') .')...');
                            $response = new Response(
                                "Website is not configured...",
                                Response::TYPE_HTML
                            );
                            return Response::output($object, $response);
                        }
                    }
                }
                if(
                    property_exists($route, 'redirect') &&
                    property_exists($route, 'method') &&
                    in_array(
                        Handler::method(),
                        $route->method
                    )
                ) {
                    $object->logger(App::LOGGER_NAME)->info('Request (' . $object->request('request') .') Redirect: ' . $route->redirect . ' Method: ' . implode(', ', $route->method));
                    Core::redirect($route->redirect);
                }
                elseif(
                    property_exists($route, 'redirect') &&
                    !property_exists($route, 'method')
                ){
                    $object->logger(App::LOGGER_NAME)->info('Redirect: ' . $route->redirect);
                    Core::redirect($route->redirect);
                }
                elseif(
                    property_exists($route, 'url')
                ){
                    $parse = new Parse($object, $object->data());
                    $route->url = $parse->compile($route->url, $object->data());
                    if(File::extension($route->url) === $object->config('extension.json')){
                        $response = new Response(
                            File::read($route->url),
                            Response::TYPE_JSON,
                        );
                        return Response::output($object, $response);
                    } else {
                        $extension = File::extension($route->url);
                        Config::contentType($object);
                        $contentType = $object->config('contentType.' . strtolower($extension));
                        if($contentType){
                            $response = new Response(
                                File::read($route->url),
                                Response::TYPE_FILE,
                            );
                            $response->header([
                                'Content-Type: ' . $contentType
                            ]);
                            return Response::output($object, $response);
                        }
                        throw new Exception('Extension (' . $extension . ') not supported...');
                    }
                } else {
                    App::contentType($object);
                    App::controller($object, $route);
                    $methods = get_class_methods($route->controller);
                    if(empty($methods)){
                        $object->logger(App::LOGGER_NAME)->error('Couldn\'t determine controller (' . $route->controller .') with request (' . $object->request('request') .')');
                        $response = new Response(
                            App::exception_to_json(new Exception(
                        'Couldn\'t determine controller (' . $route->controller .')'
                            )),
                            Response::TYPE_JSON,
                            Response::STATUS_ERROR
                        );
                        return Response::output($object, $response);
                    }
                    Config::contentType($object);
                    $functions = [];
                    if(in_array('controller', $methods)){
                        $functions[] = 'controller';
                        $route->controller::controller($object);
                    }
                    if(in_array('configure', $methods)){
                        $functions[] = 'configure';
                        $route->controller::configure($object);
                    }
                    if(in_array('before_run', $methods)){
                        $functions[] = 'before_run';
                        $route->controller::before_run($object);
                    }
                    if(in_array($route->function, $methods)){
                        $functions[] = $route->function;
                        $object->config('controller.function', $route->function);
                        $request = Core::deep_clone(
                            $object->get(
                                App::NAMESPACE . '.' .
                                Handler::NAME_REQUEST . '.' .
                                Handler::NAME_INPUT
                            )->data()
                        );
                        $object->config(
                            'request',
                            $request
                        );
                        $result = $route->controller::{$route->function}($object);
                    } else {
                        $object->logger(App::LOGGER_NAME)->error(
                            'Controller (' .
                            $route->controller .
                            ') function (' .
                            $route->function .
                            ') does not exist.'
                        );
                        $response = new Response(
                            App::exception_to_json(new Exception(
                        'Controller (' .
                                $route->controller .
                                ') function (' .
                                $route->function .
                                ') does not exist.'
                            )),
                            Response::TYPE_JSON,
                            Response::STATUS_ERROR
                        );
                        return Response::output($object, $response);
                    }
                    if(in_array('after_run', $methods)){
                        $functions[] = 'after_run';
                        $route->controller::after_run($object);
                    }
                    if(in_array('before_result', $methods)){
                        $functions[] = 'before_result';
                        $route->controller::before_result($object);
                    }
                    $functions[] = 'result';
                    $result = App::result($object, $result);
                    if(in_array('after_result', $methods)){
                        $functions[] = 'after_result';
                        $result = $route->controller::after_result($object, $result);
                    }
                    $object->logger(App::LOGGER_NAME)->info('Functions: [' . implode(', ', $functions) . '] called in controller: ' . $route->controller);
                    return $result;
                }

            } catch (Exception $exception) {
                try {
                    if($object->data(App::CONTENT_TYPE) === App::CONTENT_TYPE_JSON){
                        if(!headers_sent()){
                            header('Status: 500');
                            header('Content-Type: application/json');
                        }
                        $object->logger(App::LOGGER_NAME)->error($exception->getMessage());
                        return App::exception_to_json($exception);
                    }
                    elseif($object->data(App::CONTENT_TYPE) === App::CONTENT_TYPE_CLI){
                        $object->logger(App::LOGGER_NAME)->error($exception->getMessage());
                        fwrite(STDERR, App::exception_to_cli($object, $exception));
                        return '';
                    } else {
                        $parse = new Module\Parse($object, $object->data());
                        $url = $object->config('server.http.error.500');
                        $url = $parse->compile($url, $object->data());
                        if(!headers_sent()){
                            header('Status: 500');
                        }
                        if(File::exist($url)){
                            $parse = new Module\Parse($object, $object->data());
                            $read = File::read($url);
                            $data = [];
                            $data['exception'] = Core::object_array($exception);
                            $data['exception']['className'] = get_class($exception);
                            return $parse->compile($read, $data);
                        } else {
                            echo $exception;
                        }
                    }
                } catch (ObjectException $exception){
                    return $exception;
                }
            }
        } else {
            $object->logger(App::LOGGER_NAME)->info('File request: ' . $object->request('request') . ' called...');
            return $file;
        }
    }

    /**
     * @throws Exception
     */
    public static function controller(App $object, $route){
        if(property_exists($route, 'controller')){
            $check = class_exists($route->controller);
            if(empty($check)){
                throw new Exception('Cannot call controller (' . $route->controller .')');
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

    /**
     * @throws Exception
     */
    public static function exception_to_json(Exception $exception){
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
        //$array['trace_as_string'] = $exception->getTraceAsString(); //not needed is unclear...
        try {
            return Core::object($array, Core::OBJECT_JSON);
        } catch (ObjectException $objectException) {
            throw $exception;
        }
    }

    /**
     * @throws Exception
     */
    public static function exception_to_cli(App $object, Exception $exception){
        $class = get_class($exception);
        $width = (int) Cli::tput('width');
        $background = '200;0;0';
        $output = chr(27) . '[48;2;' . $background . 'm';
        $output .= str_repeat(' ', $width);
        $output .= PHP_EOL;
        $output .= $class . PHP_EOL;
        $output .= PHP_EOL;
        $output .= $exception->getMessage() . PHP_EOL;
        $output .= PHP_EOL;
        $output .= 'file: ' . $exception->getFile() . PHP_EOL;
        $output .= 'line: ' . $exception->getLine() . PHP_EOL;
        $output .= chr(27) . "[0m";
        $output .= PHP_EOL;
        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
            $output .= App::exception_to_json($exception);
        }
        return $output;
    }

    public static function response_output(App $object, $output=App::CONTENT_TYPE_HTML){
        $object->config('response.output', $output);
    }

    /**
     * @throws ObjectException
     * @throws Exception
     */
    private static function result(App $object, $output){
        if($output instanceof Exception){
            ddd('yes');
            if(App::is_cli()){
                $object->logger(App::LOGGER_NAME)->error($output->getMessage());
                fwrite(STDERR, App::exception_to_cli($object, $output));
                return '';
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

    /**
     * @throws Exception
     */
    public function logger($name='App', $logger=null): Logger
    {
        if($logger !== null){
            $this->setLogger($name, $logger);
        }
        return $this->getLogger($name);
    }

    private function setLogger($name='App', Logger $logger=null){
        $this->logger[$name] = $logger;
    }

    /**
     * @throws Exception
     */
    private function getLogger($name='App'): Logger
    {
        if(array_key_exists($name, $this->logger)){
            return $this->logger[$name];
        }
        throw new Exception('Logger with name: ' . $name . ' not initialised.');
    }

    public function route(){
        return $this->data(App::ROUTE);
    }

    public function config($attribute=null, $value=null){
        return $this->data(App::CONFIG)->data($attribute, $value);
    }

    public function request($attribute=null, $value=null, $type=null){
        return $this->data(App::REQUEST)->data($attribute, $value, $type=null);
    }

    public static function parameter($object, $parameter='', $offset=0){
        return parent::parameter($object->data(App::REQUEST)->data(), $parameter, $offset);
    }

    public static function flags($object): array
    {
        return parent::flags($object->data(App::REQUEST)->data());
    }

    public static function options($object): array
    {
        return parent::options($object->data(App::REQUEST)->data());
    }

    /**
     * @throws Exception
     */
    public function session($attribute=null, $value=null){
        return Handler::session($attribute, $value);
    }

    public function cookie($attribute=null, $value=null, $duration=null){
        return Handler::cookie($attribute, $value, $duration);
    }

    public function upload($number=null): Data
    {
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

    public function server($attribute){
        if(array_key_exists($attribute, $_SERVER)){
            return $_SERVER[$attribute];
        }
    }

    /**
     * @throws ObjectException
     */
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

    /**
     * @throws ObjectException
     * @throws FileWriteException
     */
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
                $readback = [
                    'script',
                    'link'
                ];
                foreach($readback as $name){
                    $temp = Parse::readback($this, $parse, $name);
                    if(!empty($temp)){
                        $temp_old = $data->data($name);
                        if(empty($temp_old)){
                            $temp_old = [];
                        }
                        $temp = array_merge($temp_old, $temp);
                        $data->data($name, $temp);
                    }
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

    public static function is_cli() : bool
    {
        if(!defined('IS_CLI')){
            return Core::is_cli();
        } else {
            return true;
        }
    }

    /**
     * @throws Exception
     */
    public static function instance($configuration=[]): App
    {
        $dir_vendor = Dir::name(__DIR__, 3);
        $autoload = $dir_vendor . 'autoload.php';
        $autoload = require $autoload;
        $config = new Config([
            'dir.vendor' => $dir_vendor,
            ...$configuration
        ]);
        return new App($autoload, $config);
    }
}
