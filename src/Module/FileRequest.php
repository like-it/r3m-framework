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

    private static function get_default_location(App $object, $dir): array
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
        Config::server_fileRequest_local($object);
        $fileRequest = $object->config('server.fileRequest');
        $has_location = false;
        Config::contentType($object);
        if(empty($fileRequest)){
            $location = FileRequest::get_default_location($object, $dir);
            $has_location = true;
        } else {
            d($object->config());
            ddd($fileRequest);
        }


        /*
        $node = false;
        if($subdomain){
            $attribute = 'server.fileRequest.' . $subdomain . '-' . $domain . '-' . $extension;
            $node = $object->config($attribute);
        } else {
            $attribute = 'server.fileRequest.' . $domain . '-' . $extension;
            $node = $object->config($attribute);
        }
        if(empty($node)){
            $fileRequest = $object->config('server.fileRequest');
        }
        $location = [];
        $has_location = false;
        Config::contentType($object);
        if(empty($node) && empty($fileRequest)) {
            $location = FileRequest::get_default_location($object, $dir);
            $has_location = true;
        }
        elseif(
            is_object($fileRequest) &&
            property_exists($fileRequest, 'location')
        ) {
            $parse = new Parse($object);
            $fileRequest = $parse->compile($fileRequest, $object->data());
            $location = $fileRequest->location;
            $has_location = true;
        }
        elseif(
            is_object($fileRequest) &&
            property_exists($fileRequest, 'location') &&
            is_string($fileRequest->location) &&
            substr($fileRequest->location, 0, 2) === '{{' &&
            substr($fileRequest->location, -2, 2) === '}}'
        ){
            $parse = new Parse($object);
            $fileRequest = $parse->compile($fileRequest, $object->data());
            $location = $fileRequest->location;
            $has_location = true;
        }
        elseif(is_object($fileRequest)){
            ddd($object->config());
            $parse = new Parse($object);
            $fileRequest = $parse->compile($fileRequest, $object->data());
            foreach($fileRequest as $host => $node){
                $explode = explode('-', $host, 3);
                $count = count($explode);
                if($count === 3){
                    if($subdomain . '-' . $domain . '-' . $extension === $host){
                        if(
                            property_exists($fileRequest, $host) &&
                            property_exists($fileRequest->$host, 'location')
                        ){
                            $location = $fileRequest->$host->location;
                            $has_location = true;
                            break;
                        }
                    }
                }
                elseif($count === 2){
                    if($domain . '-' . $extension === $host){
                        if(
                            property_exists($fileRequest, $host) &&
                            property_exists($fileRequest->$host, 'location')
                        ){
                            $location = $fileRequest->$host->location;
                            $has_location = true;
                            break;
                        }
                    }
                }
            }
        }
        */
        if($has_location === false){
            $location = FileRequest::get_default_location($object, $dir);
        }
        ddd($location);
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

                    if(array_key_exists('HTTP_REFERER', $_SERVER)){
                        $origin = rtrim($_SERVER['HTTP_REFERER'], '/');
                        if(Core::cors_is_allowed($object, $origin)){
                            header("Access-Control-Allow-Origin: {$origin}");
                        }
                    }
                }
                return File::read($url);
            }
        }
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