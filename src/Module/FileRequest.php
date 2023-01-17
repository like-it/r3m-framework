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
namespace R3m\Io\Module;

use R3m\Io\App;
use R3m\Io\Config;
use Exception;
use R3m\Io\Exception\LocateException;

class FileRequest {
    const REQUEST = 'Request';

    private static function location(App $object, $dir): array
    {
        $location = [];
        $explode = explode('/', $dir);
        $controller = array_shift($explode);
        $view = $explode;
        array_unshift($explode, 'Public');
        if (!empty($controller)) {
            array_unshift($explode, $controller);
        }
        array_unshift($view, 'Public');
        $view_2 = $view;
        array_unshift($view, 'View');
        if (!empty($controller)) {
            array_unshift($view, $controller);
            array_unshift($view_2, $controller);
        }
        array_unshift($view_2, 'View');
        $location[] = $object->config('host.dir.root') .
            rtrim(implode($object->config('ds'), $view), '/') .
            $object->config('ds');
        $location[] = $object->config('host.dir.root') .
            rtrim(implode($object->config('ds'), $view_2), '/') .
            $object->config('ds');
        $location[] = $object->config('host.dir.root') .
            rtrim(implode($object->config('ds'), $explode), '/') .
            $object->config('ds');
        $location[] = $object->config('host.dir.root') .
            $dir .
            'Public' .
            $object->config('ds');
        $explode = explode('/', $dir);
        array_pop($explode);
        $type = array_pop($explode);
        array_push($explode, '');
        $dir_type = implode('/', $explode);
        if ($type) {
            $location[] = $object->config('host.dir.root') .
                $dir_type .
                'Public' .
                $object->config('ds') .
                $type .
                $object->config('ds');
        }
        $location[] = $object->config('host.dir.root') .
            'View' .
            $object->config('ds') .
            $dir .
            'Public' .
            $object->config('ds');
        if ($type) {
            $location[] = $object->config('host.dir.root') .
                'View' .
                $object->config('ds') .
                $dir_type .
                'Public' .
                $object->config('ds') .
                $type .
                $object->config('ds');
        }
        $location[] = $object->config('host.dir.public') .
            $dir;
        $location[] = $object->config('project.dir.public') .
            $dir;
        return $location;
    }

    public static function local(App $object){
        $fileRequest = $object->config('server.fileRequest');
        if(empty($fileRequest)){
            return false;
        }
        if(!is_object($fileRequest)){
            return false;
        }
        foreach($fileRequest as $name => $node){
            $explode = explode('-', $name, 3);
            $count = count($explode);
            if($count > 1){
                $extension = $explode[$count - 1];
                $explode[$count - 1] = 'local';
                $name = implode('-', $explode);
                $fileRequest->{$name} = $node;
            }
        }
    }

    /**
     * @throws LocateException
     * @throws Exception
     */
    public static function get(App $object)
    {
        if (
            array_key_exists('REQUEST_METHOD', $_SERVER) &&
            $_SERVER['REQUEST_METHOD'] == 'OPTIONS'
        ) {
            Core::cors($object);
        }
        if (
            $object->config('server.http.upgrade_insecure') === true &&
            array_key_exists('REQUEST_SCHEME', $_SERVER) &&
            array_key_exists('REQUEST_URI', $_SERVER) &&
            $_SERVER['REQUEST_SCHEME'] === Host::SCHEME_HTTP &&
            $object->config('framework.environment') !== Config::MODE_DEVELOPMENT &&
            Host::isIp4Address() === false
        ) {
            $subdomain = Host::subdomain();
            $domain = Host::domain();
            $extension = Host::extension();
            if ($subdomain) {
                $url = Host::SCHEME_HTTPS . '://' . $subdomain . '.' . $domain . '.' . $extension . $_SERVER['REQUEST_URI'];
            } else {
                $url = Host::SCHEME_HTTPS . '://' . $domain . '.' . $extension . $_SERVER['REQUEST_URI'];
            }
            Core::redirect($url);
        }
        $request = $object->data(App::REQUEST);
        $input = $request->data('request');
        $dir = str_replace(['../', '..'], '', Dir::name($input));
        $file = str_replace($dir, '', $input);
        if (
            (
                substr($file, 0, 3) === '%7B' &&
                substr($file, -3, 3) === '%7D'
            ) ||
            (
                substr($file, 0, 1) === '[' &&
                substr($file, -1, 1) === ']'
            )
        ) {
            return false;
        }
        $file_extension = File::extension($file);
        if (empty($file_extension)) {
            return false;
        }
        $subdomain = Host::subdomain();
        $domain = Host::domain();
        $extension = Host::extension();
        $config = $object->data(App::CONFIG);
        FileRequest::local($object);
        $fileRequest = $object->config('server.fileRequest');
        Config::contentType($object);
        if(empty($fileRequest)){
            $location = FileRequest::location($object, $dir);
        } else {
            $config_mtime = false;
            $config_url = $object->config('project.dir.data') . 'Config' . $object->config('extension.json');
            $cache_url = $object->config('framework.dir.cache') . 'FileRequest' . $object->config('extension.json');
            if(File::exist($config_url)){
                $config_mtime = File::mtime($config_url);
            }
            if(File::exist($cache_url)){
                $cache_mtime = File::mtime($cache_url);
                if($cache_mtime === $config_mtime){
                    //read cache_url
                    $data = $object->data_read($cache_url);
                } else {
                    //write cache_url
                    $parse = new Parse($object);
                    $fileRequest = $parse->compile($fileRequest, $object->data());
                    $data = new Data($fileRequest);
                    $data->write($cache_url);
                    File::touch($cache_url, $config_mtime);
                }
            } else {
                //write cache_url
                $parse = new Parse($object);
                $fileRequest = $parse->compile($fileRequest, $object->data());
                $data = new Data($fileRequest);
                $data->write($cache_url);
                File::touch($cache_url, $config_mtime);
            }
            if($subdomain){
                $attribute = $subdomain . '-' . $domain . '-' . $extension . '.location';
            } else {
                $attribute = $domain . '-' . $extension. '.location';
            }
            $location = $data->get($attribute);
            if(empty($location)){
                $location = $data->get('location');
            }
            if(empty($location)){
                $location = FileRequest::location($object, $dir);
            }
        }
        foreach($location as $url){
            if(substr($url, -1, 1) !== $object->config('ds')){
                $url .= $object->config('ds');
            }
            $url .= $file;
            if(File::exist($url)){
                $etag = sha1($url);
                $mtime = File::mtime($url);
                $contentType = $object->config('contentType.' . $file_extension);
                if(empty($contentType)){
                    Handler::header('HTTP/1.0 415 Unsupported Media Type', 415);
                    if($config->data('framework.environment') === Config::MODE_DEVELOPMENT){
                        $json = [];
                        $json['message'] = 'HTTP/1.0 415 Unsupported Media Type';
                        $json['file'] = $file;
                        $json['extension'] = $file_extension;
                        $json['available'] = $config->data('contentType');
                        echo Core::object($json, Core::OBJECT_JSON);
                    }
                    exit();
                }
                if(!headers_sent()){
                    $gm = gmdate('D, d M Y H:i:s T', $mtime);
                    Handler::header('Last-Modified: '. $gm);
                    Handler::header('Content-Type: ' . $contentType);
                    Handler::header('ETag: ' . $etag . '-' . $gm);
                    Handler::header('Cache-Control: public');

                    //$object->logger('App')->debug('server', $_SERVER);

                    if(array_key_exists('HTTP_REFERER', $_SERVER)){
                        $origin = rtrim($_SERVER['HTTP_REFERER'], '/');
                        if(Core::cors_is_allowed($object, $origin)){
                            $object->logger('App')->debug('cors is allowed for: ', [ $origin ]);
                            header("Access-Control-Allow-Origin: *");
//                            header("Access-Control-Allow-Origin: {$origin}");
                        } else {
                            $object->logger('App')->debug('cors is not allowed for: ', [ $origin ]);
                        }
                    } else {
                        $object->logger('App')->debug('No HTTP_REFERER');
                    }
                } else {
                    $object->logger('App')->debug('Headers sent');
                }
                return File::read($url);
            } 
        }
        $object->logger('App')->debug('File doesn\'t exists', [ $url ]);
        Handler::header('HTTP/1.0 404 Not Found', 404);
        if($config->data('framework.environment') === Config::MODE_DEVELOPMENT){
            throw new LocateException('Cannot find location for file:' . "<br>\n" . implode("<br>\n", $location), $location);
        } else {
            if(
                in_array(
                    $extension,
                    $config->get('error.extension.tpl')
                )
            ){
                if($config->data('server.http.error.404')){
                    //let's parse this tpl
                    $data = new Data();
                    $data->set('file', $file);
                    $data->set('extension', $extension);
                    $data->set('location', $location);
                    $contentType = $config->data('contentType.' . $extension);
                    $data->set('contentType', $contentType);
                    $parse = new Parse($object, $data);
                    $compile = $parse->compile(File::read($parse->compile($config->data('server.http.error.404'), $data->get())), $data->get());
                    echo $compile;
                }
            }
            elseif(
                in_array(
                    $extension,
                    $config->get('error.extension.text')
                )
            ){
                if($config->data('server.http.error.404')){
                    echo "HTTP/1.0 404 Not Found: " . $file . PHP_EOL;
                }
            }
            elseif(
                in_array(
                    $extension,
                    $config->get('error.extension.js')
                )
            ){
                if($config->data('server.http.error.404')){
                    echo 'console.error("HTTP/1.0 404 Not Found",  "' . $file . '");';
                }
            }
            elseif(
                in_array(
                    $extension,
                    $config->get('error.extension.json')
                )
            ){
                $contentType = 'application/json';
                Handler::header('Content-Type: ' . $contentType, null, true);
                echo '{
    "file" : "' . $file . '",
    "extension" : "' . $extension . '",
    "contentType" : "' . $contentType . '",
    "message" : "Error: cannot find file."
}';
            }
        }
        $object->logger()->error('HTTP/1.0 404 Not Found', $location);
        exit();
    }

}