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

use stdClass;

use R3m\Io\Module\Autoload;
use R3m\Io\Module\Cli;
use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\Database;
use R3m\Io\Module\Dir;
use R3m\Io\Module\Event;
use R3m\Io\Module\File;
use R3m\Io\Module\FileRequest;
use R3m\Io\Module\Filter;
use R3m\Io\Module\Handler;
use R3m\Io\Module\Host;
use R3m\Io\Module\Logger;
use R3m\Io\Module\Middleware;
use R3m\Io\Module\OutputFilter;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Response;
use R3m\Io\Module\Route;
use R3m\Io\Module\Server;

use Psr\Log\LoggerInterface;

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
    const EVENT = App::NAMESPACE . '.' . Event::NAME;
    const MIDDLEWARE = App::NAMESPACE . '.' . Middleware::NAME;
    const OUTPUTFILTER = App::NAMESPACE . '.' . OutputFilter::NAME;
    const FILTER = App::NAMESPACE . '.' . Filter::NAME;
    const FLAGS = App::NAMESPACE . '.' . Data::FLAGS;
    const OPTIONS = App::NAMESPACE . '.' . Data::OPTIONS;
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
        $this->data(App::EVENT, new Data());
        $this->data(App::MIDDLEWARE, new Data());
        $this->data(App::OUTPUTFILTER, new Data());
        App::is_cli();
        require_once __DIR__ . '/Debug.php';
        require_once __DIR__ . '/Error.php';
        Config::configure($this);
        Host::configure($this);
        Logger::configure($this);
        Event::configure($this);
        Middleware::configure($this);
        OutputFilter::configure($this);
        Autoload::configure($this);
        Autoload::ramdisk_configure($this);
    }

    /**
     * @throws Exception
     */
    public static function configure(App $object){
        $info = 'Logger: App initialised.';
        if(App::is_cli() === false){
            $domains = $object->config('server.cors.domains');
            if(!empty($domains)){
                $info .= ' and enabling cors';
                Server::cors($object);
            }
        }
        if(
            !empty($object->config('project.log.name')) &&
            empty($object->request('request'))
        ){
            $object->logger($object->config('project.log.name'))->info($info, [Host::subdomain()]);
        }
        elseif(
            !empty($object->config('project.log.name'))
        ) {
            $object->logger($object->config('project.log.name'))->info($info . ' with request: ' . $object->request('request'), [Host::subdomain()]);
        }
        $options = $object->config('server.http.cookie');
        if(
            is_object($options) &&
            property_exists($options, 'domain') &&
            $options->domain === true
        ){
            if(App::is_cli()){
                unset($options->domain);
            } else {
                $options->domain = Server::url($object,Host::domain() . '.' . Host::extension());
                if(!$options->domain){
                    $options->domain = Host::domain() . '.' . Host::extension();
                }
            }
            $options->secure = null;
            if(Host::scheme() === Host::SCHEME_HTTPS){
                $options->secure = true;
            }
            Handler::session_set_cookie_params($options);
        }
    }

    /**
     * @throws Exception
     * @throws ObjectException
     * @throws LocateException
     */
    public static function run(App $object){
        Handler::request_configure($object);
        App::configure($object);
        Route::configure($object);
        $route = false;
        $logger = $object->config('project.log.name');
        try {
            $file = FileRequest::get($object);
            if ($file === false) {
                $route = Route::request($object);
                if ($route === false) {
                    if ($object->config('framework.environment') === Config::MODE_DEVELOPMENT) {
                        if($logger){
                            $object->logger($logger)->error('Couldn\'t determine route (' . $object->request('request') . ')...');
                        }
                        $exception = new Exception(
                            'Couldn\'t determine route (' . $object->request('request') . ')...'
                        );
                        $response = new Response(
                            App::exception_to_json($exception),
                            Response::TYPE_JSON,
                            Response::STATUS_ERROR
                        );
                        Event::trigger($object, 'app.run.route.error', [
                            'route' => false,
                            'exception' => $exception
                        ]);
                        return Response::output($object, $response);
                    } else {
                        $route = Route::wildcard($object);
                        if ($route === false) {
                            if($logger){
                                $object->logger($logger)->error('Couldn\'t determine route (wildcard) (' . $object->request('request') . ')...');
                            }
                            $response = new Response(
                                "Website is not configured...",
                                Response::TYPE_HTML
                            );
                            Event::trigger($object, 'app.run.route.wildcard.error', [
                                'route' => false,
                                'is_not_configured' => true
                            ]);
                            return Response::output($object, $response);
                        }
                    }
                }
                if (
                    property_exists($route, 'redirect') &&
                    property_exists($route, 'method') &&
                    in_array(
                        Handler::method(),
                        $route->method,
                        true
                    )
                ) {
                    if($logger){
                        $object->logger($logger)->info('Request (' . $object->request('request') . ') Redirect: ' . $route->redirect . ' Method: ' . implode(', ', $route->method));
                    }
                    Event::trigger($object, 'app.run.route.redirect', [
                        'route' => $route,
                    ]);
                    Core::redirect($route->redirect);
                } elseif (
                    property_exists($route, 'redirect') &&
                    !property_exists($route, 'method')
                ) {
                    if($logger){
                        $object->logger($logger)->info('Redirect: ' . $route->redirect);
                    }
                    Event::trigger($object, 'app.run.route.redirect', [
                        'route' => $route,
                    ]);
                    Core::redirect($route->redirect);
                } elseif (
                    property_exists($route, 'url')
                ) {
                    $parse = new Parse($object, $object->data());
                    $route->url = $parse->compile($route->url, $object->data());
                    if (File::extension($route->url) === $object->config('extension.json')) {
                        $response = new Response(
                            File::read($route->url),
                            Response::TYPE_JSON,
                        );
                        Event::trigger($object, 'app.run.route.file', [
                            'route' => $route,
                            'extension' => $object->config('extension.json'),
                            'content_type' => $object->config('contentType.' . strtolower($object->config('extension.json')))
                        ]);
                        return Response::output($object, $response);
                    } else {
                        $extension = File::extension($route->url);
                        Config::contentType($object);
                        $contentType = $object->config('contentType.' . strtolower($extension));
                        if ($contentType) {
                            $response = new Response(
                                File::read($route->url),
                                Response::TYPE_FILE,
                            );
                            $response->header([
                                'Content-Type: ' . $contentType
                            ]);
                            Event::trigger($object, 'app.run.route.file', [
                                'route' => $route,
                                'extension' => $extension,
                                'content_type' => $contentType
                            ]);
                            return Response::output($object, $response);
                        }
                        throw new Exception('Extension (' . $extension . ') not supported...');
                    }
                } else {
                    App::contentType($object);
                    App::controller($object, $route);
                    $methods = get_class_methods($route->controller);
                    if (empty($methods)) {
                        if($logger){
                            $object->logger($logger)->error('Couldn\'t determine controller (' . $route->controller . ') with request (' . $object->request('request') . ')');
                        }
                        $exception = new Exception(
                            'Couldn\'t determine controller (' . $route->controller . ')'
                        );
                        $response = new Response(
                            App::exception_to_json($exception),
                            Response::TYPE_JSON,
                            Response::STATUS_ERROR
                        );
                        Event::trigger($object, 'app.run.route.file', [
                            'route' => $route,
                            'exception' => $exception
                        ]);
                        return Response::output($object, $response);
                    }
                    Config::contentType($object);
                    $functions = [];
                    if (in_array('controller', $methods, true)) {
                        $functions[] = 'controller';
                        $route->controller::controller($object);
                    }
                    if (in_array('configure', $methods, true)) {
                        $functions[] = 'configure';
                        $route->controller::configure($object);
                    }
                    // @deprecated since Middleware
                    if (in_array('before_run', $methods, true)) {
                        $functions[] = 'before_run';
                        $route->controller::before_run($object);
                    }
                    $route = Middleware::trigger($object, [
                        'route' => $route,
                        'methods' => $methods,
                    ]);
                    if (in_array($route->function, $methods, true)) {
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
                        Event::trigger($object, 'app.run.route.controller', [
                            'route' => $route,
                            'response' => $result
                        ]);
//                        $start = microtime(true);
                        $result = OutputFilter::trigger($object, [
                            'route' => $route,
                            'methods' => $methods,
                            'response' => $result
                        ]);
                        /*
                        $duration = microtime(true) - $start;
                        if($duration < 1) {
                            echo 'Duration: ' . round($duration * 1000, 2) . ' msec ' . PHP_EOL;
                        } else {
                            echo 'Duration: ' . round($duration, 2) . ' sec'  . PHP_EOL;
                        }
                        */
                    } else {
                        $object->logger(App::LOGGER_NAME)->error(
                            'Controller (' .
                            $route->controller .
                            ') function (' .
                            $route->function .
                            ') does not exist.'
                        );
                        $exception = new Exception(
                            'Controller (' .
                            $route->controller .
                            ') function (' .
                            $route->function .
                            ') does not exist.'
                        );
                        $response = new Response(
                            App::exception_to_json($exception),
                            Response::TYPE_JSON,
                            Response::STATUS_ERROR
                        );
                        Event::trigger($object, 'app.run.route.controller', [
                            'route' => $route,
                            'exception' => $exception
                        ]);
                        return Response::output($object, $response);
                    }
                    // @deprecated since OutputFilter
                    if (in_array('after_run', $methods, true)) {
                        $functions[] = 'after_run';
                        $route->controller::after_run($object);
                    }
                    // @deprecated since OutputFilter
                    if (in_array('before_result', $methods, true)) {
                        $functions[] = 'before_result';
                        $route->controller::before_result($object);
                    }
                    $functions[] = 'result';
                    $result = App::result($object, $result);
                    // @deprecated since OutputFilter
                    if (in_array('after_result', $methods)) {
                        $functions[] = 'after_result';
                        $result = $route->controller::after_result($object, $result);
                    }
                    if($logger){
                        $object->logger($logger)->info('Functions: [' . implode(', ', $functions) . '] called in controller: ' . $route->controller);
                    }
                    return $result;
                }
            }  else {
                if($logger){
                    $object->logger($logger)->info('File request: ' . $object->request('request') . ' called...');
                }
                Event::trigger($object, 'app.run.file.request', []);
                return $file;
            }
        }
        catch (Exception | LocateException $exception) {
            try {
                if($object->data(App::CONTENT_TYPE) === App::CONTENT_TYPE_JSON){
                    if(!headers_sent()){
                        header('Status: 500');
                        header('Content-Type: application/json');
                    }
                    if($logger){
                        $object->logger($logger)->error($exception->getMessage());
                    }
                    Event::trigger($object, 'app.route.exception', [
                        'route' => $route,
                        'exception' => $exception
                    ]);
                    return App::exception_to_json($exception);
                }
                elseif($object->data(App::CONTENT_TYPE) === App::CONTENT_TYPE_CLI){
                    if($logger){
                        $object->logger($logger)->error($exception->getMessage());
                    }
                    Event::trigger($object, 'app.route.exception', [
                        'route' => $route,
                        'exception' => $exception
                    ]);
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
                        Event::trigger($object, 'app.route.exception', [
                            'route' => $route,
                            'url' => $url,
                            'exception' => $exception
                        ]);
                        return $parse->compile($read, $data);
                    } else {
                        Event::trigger($object, 'app.route.exception', [
                            'route' => $route,
                            'exception' => $exception
                        ]);
                        echo $exception;
                    }
                }
            } catch (ObjectException $exception){
                return $exception;
            }
        }
        return null;
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
            $array['location'] = $exception->getLocation($array);
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
    public static function exception_to_cli(App $object, Exception $exception): string
    {
        $class = get_class($exception);
        $width = Cli::tput('width') + 0;
        $background = '200;0;0';
        $output = chr(27) . '[48;2;' . $background . 'm';
        $output .= str_repeat(' ', $width);
        $output .= PHP_EOL;
        $output .= $class . PHP_EOL;
        $output .= PHP_EOL;
        $output .= $exception->getMessage() . PHP_EOL;
        $output .= 'file: ' . $exception->getFile() . PHP_EOL;
        $output .= 'line: ' . $exception->getLine() . PHP_EOL;
        $output .= chr(27) . "[0m";
        $output .= PHP_EOL;
        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
            $output .= (string) $exception;
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
            if(App::is_cli()){
                $logger = $object->config('project.log.name');
                if($logger){
                    $object->logger($logger)->error($output->getMessage());
                }
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
    public function logger($name='', $logger=null): LoggerInterface
    {
        if($logger !== null){
            $this->setLogger($name, $logger);
        }
        return $this->getLogger($name);
    }

    /**
     * @throws Exception
     */
    private function setLogger($name='', LoggerInterface $logger=null){
        if(empty($name)){
            $name = $this->config('project.log.name');
        }
        if(empty($name)){
            throw new Exception('PLease configure project.log.name or provide a name');
        }
        $name = ucfirst($name);
        $this->logger[$name] = $logger;
    }

    /**
     * @throws Exception
     */
    private function getLogger($name=''): LoggerInterface
    {
        if(empty($name)){
            $name = $this->config('project.log.name');
        }
        if(empty($name)){
            throw new Exception('PLease configure project.log.name or provide a name');
        }
        $name = ucfirst($name);
        if(array_key_exists($name, $this->logger)){
            return $this->logger[$name];
        }
        if($name === 'Project.log.name'){
            $debug = debug_backtrace(true);
            d($debug[0]['file'] . ' ' . $debug[0]['line']);
            d($debug[1]['file'] . ' ' . $debug[1]['line']);
            d($debug[2]['file'] . ' ' . $debug[2]['line']);
            throw new Exception('Please configure project.log.name');
        }
        d($name);
        ddd($this->logger);
        throw new Exception('Logger with name: ' . $name . ' not initialised.');

    }

    public function route(){
        return $this->data(App::ROUTE);
    }

    public function config($attribute=null, $value=null){
        return $this->data(App::CONFIG)->data($attribute, $value);
    }

    public function event($attribute=null, $value=null){
        return $this->data(App::EVENT)->data($attribute, $value);
    }

    public function middleware($attribute=null, $value=null){
        return $this->data(App::MIDDLEWARE)->data($attribute, $value);
    }

    public function request($attribute=null, $value=null){
        return $this->data(App::REQUEST)->data($attribute, $value);        
    }

    public static function parameter($object, $parameter='', $offset=0){
        return parent::parameter($object->data(App::REQUEST)->data(), $parameter, $offset);
    }

    public static function flags($object): stdClass
    {
        $flags = $object->data(App::FLAGS);
        if(empty($flags)){
            $flags = parent::flags($object->data(App::REQUEST)->data());
            $object->data(App::FLAGS, $flags);
        }
        return $flags;
    }

    public static function options($object): stdClass
    {
        $options = $object->data(App::OPTIONS);
        if(empty($options)){
            $options = parent::options($object->data(App::REQUEST)->data());
            $object->data(App::OPTIONS, $options);
        }
        return $options;
    }

    /**
     * @throws Exception
     */
    public function session($attribute=null, $value=null){
        return Handler::session($attribute, $value);
    }

    public function cookie($attribute=null, $value=null, $duration=null){
        if($attribute === 'http'){
            $cookie = $this->server('HTTP_COOKIE');
            return explode('; ', $cookie);
        }
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
        if($attribute===null){
            return $_SERVER;
        }
        if(array_key_exists($attribute, $_SERVER)){
            return $_SERVER[$attribute];
        }
        return null;
    }

    /**
     * @throws ObjectException
     * @throws FileWriteException
     */
    public function data_select($url, $select=null): Data
    {
        $parse = new Parse($this);
        $data = new Data();
        $data->data($this->data());
        $node = new Data();
        $node->data(
            Core::object_select(
                $parse,
                $data,
                $url,
                $select,
                false,
            )
        );
        return $node;
    }

    /**
     * @throws ObjectException
     * @throws FileWriteException
     */
    public function parse_select($url, $select=null, $scope='scope:object'): Data
    {
        $parse = new Parse($this);
        $data = new Data();
        $data->data($this->data());
        $node = new Data();
        $node->data(
            Core::object_select(
                $parse,
                $data,
                $url,
                $select,
                true,
                $scope
            )
        );
        return $node;
    }

    /**
     * @throws ObjectException
     * @throws FileWriteException
     */
    public function object_select($url, $select=null, $compile=false, $scope='scope:object')
    {
        $parse = new Parse($this);
        $data = new Data();
        $data->data($this->data());
        return Core::object_select(
            $parse,
            $data,
            $url,
            $select,
            $compile,
            $scope
        );
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
                try {
                    $data = new Data();
                    $data->do_not_nest_key($do_not_nest_key);
                    $data->data(Core::object($read, Core::OBJECT_OBJECT));
                }
                catch(ObjectException $exception){
                    throw new ObjectException('Syntax error in ' . $url);
                }
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
     * @throws Exception
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
                $read = $parse->compile(Core::object($read), $data, $parse->storage(), null, true);
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

    /**
     * @throws ObjectException
     * @throws Exception
     */
    public function ramdisk_load($load=''){
        $prefixes = $this->config('ramdisk.autoload.prefix');
        if(
            !empty($prefixes) &&
            is_array($prefixes)
        ){
            foreach($prefixes as $prefix){
                $is_not = false;
                if(substr($prefix, 0, 1) === '!'){
                    $prefix = substr($prefix, 1);
                    $is_not = true;
                }
                $load_part = substr($load, 0, strlen($prefix));
                if($is_not && $load_part === $prefix){
                    return false;
                }
                if($load_part !== $prefix){
                    continue;
                }
                $part = str_replace('R3m\\Io\\', '', $load);
                $part = str_replace('\\', '/', $part);
                $url = $this->config('framework.dir.source') . $part . $this->config('extension.php');
                $ramdisk_dir = false;
                $ramdisk_url = false;
                if(
                    $this->config('ramdisk.url') &&
                    empty($this->config('ramdisk.is.disabled'))
                ){
                    $ramdisk_dir = $this->config('ramdisk.url') .
                        $this->config(Config::POSIX_ID) .
                        $this->config('ds') .
                        App::NAME .
                        $this->config('ds')
                    ;
                    $ramdisk_url = $ramdisk_dir .
                        str_replace('/', '_', $part) .
                        $this->config('extension.php')
                    ;
                }
                $config_dir = $this->config('ramdisk.url') .
                    $this->config(Config::POSIX_ID) .
                    $this->config('ds') .
                    App::NAME .
                    $this->config('ds')
                ;
                $config_url = $config_dir .
                    'File.Mtime' .
                    $this->config('extension.json')
                ;
                $mtime = $this->get(sha1($config_url));
                if(empty($mtime)){
                    $mtime = [];
                    if(file_exists($config_url)){
                        $mtime = file_get_contents($config_url);
                        if($mtime){
                            $mtime = json_decode($mtime, true);
                            $this->set(sha1($config_url), $mtime);
                        }
                    }
                }
                if(
                    file_exists($ramdisk_url) &&
                    array_key_exists(sha1($ramdisk_url), $mtime) &&
                    filemtime($ramdisk_url) === filemtime($mtime[sha1($ramdisk_url)])
                ){
                    require_once $ramdisk_url;
                }
                elseif(file_exists($url)){
                    require_once $url;
                    if(
                        $ramdisk_dir &&
                        $ramdisk_url &&
                        $config_dir &&
                        $config_url
                    ){
                        //copy to ramdisk
                        //save filemtime
                        if(!is_dir($ramdisk_dir)){
                            mkdir($ramdisk_dir, 0750, true);
                        }
                        $read = file_get_contents($url);
                        $require = $this->config('ramdisk.autoload.require');
                        $is_require = false;
                        if(
                            !empty($require) &&
                            is_array($require) &&
                            in_array($load, $require, true)
                        ) {
                            $is_require = true;
                        }
                        if($is_require === false && Autoload::ramdisk_exclude_load($this, $load)){
                            //nothing to do...
                        }
                        elseif($is_require === false && Autoload::ramdisk_exclude_content($this, $read, $url)){
                            d($read);
                            d($load);
                            d($url);
                            //files with content __DIR__, __FILE__ cannot be cached
                            //save to /tmp/r3m/io/.../Autoload/Disable.Cache.json
                            ddd('exclude_content');
                        } else {
                            file_put_contents($ramdisk_url, $read);
                            touch($ramdisk_url, filemtime($url));
                            $mtime[sha1($ramdisk_url)] = $url;
                            if(!is_dir($config_dir)){
                                mkdir($config_dir, 0750, true);
                            }
                            file_put_contents($config_url, json_encode($mtime, JSON_PRETTY_PRINT));
                            $this->set(sha1($config_url), $mtime);
                            exec('chmod 640 ' . $ramdisk_url);
                            exec('chmod 640 ' . $config_url);
                        }
                    }
                }
            }
        }
        return false;
    }
}
