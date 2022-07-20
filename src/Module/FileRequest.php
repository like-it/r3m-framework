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

    /**
     * @throws LocateException
     * @throws Exception
     */
    public static function get(App $object){
        if (
            array_key_exists('REQUEST_METHOD', $_SERVER) &&
            $_SERVER['REQUEST_METHOD'] == 'OPTIONS'
        ) {
            Core::cors();
        }
        $request = $object->data(App::REQUEST);
        $input = $request->data('request');
        $dir = str_replace(['../','..'], '', Dir::name($input));
        $file = str_replace($dir,'', $input);
        $extension = File::extension($file);
        if(empty($extension)){
            return false;
        }
        $config = $object->data(App::CONFIG);
        $location = [];
        $explode = explode('/', $dir);
        $controller = array_shift($explode);
        $view = $explode;
        array_unshift($explode, 'Public');
        if(!empty($controller)) {
            array_unshift($explode, $controller);
        }
        array_unshift($view, 'Public');
        array_unshift($view, 'View');
        if(!empty($controller)){
            array_unshift($view, $controller);
        }
        $location[] = $config->data('host.dir.root') .
            rtrim(implode($config->data('ds'), $view), '/') .
            $config->data('ds') .
            $file;
        $location[] = $config->data('host.dir.root') .
            rtrim(implode($config->data('ds'), $explode), '/') .
            $config->data('ds') .
            $file;
        $location[] = $config->data('host.dir.root') .
            $dir .
            'Public' .
            $config->data('ds') .
            $file;
        $explode = explode('/', $dir);
        array_pop($explode);
        $type = array_pop($explode);
        array_push($explode, '');
        $dir_type = implode('/', $explode);
        if($type){
            $location[] = $config->data('host.dir.root') .
                $dir_type .
                'Public' .
                $config->data('ds') .
                $type .
                $config->data('ds') .
                $file;
        }
        $location[] = $config->data('host.dir.root') .
            'View' .
            $config->data('ds') .
            $dir .
            'Public' .
            $config->data('ds') .
            $file;
        if($type){
            $location[] = $config->data('host.dir.root') .
                'View' .
                $config->data('ds') .
                $dir_type .
                'Public' .
                $config->data('ds') .
                $type .
                $config->data('ds') .
                $file;
        }
        $location[] = $config->data('host.dir.public') .
            $dir .
            $file;
        $location[] = $config->data('project.dir.public') .
            $dir .
            $file;
        foreach($location as $url){
            if(File::exist($url)){
                $etag = sha1($url);
                $mtime = File::mtime($url);
                $contentType = $config->data('contentType.' . $extension);
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
                    $allow = $object->config('server.origin.allow');
                    $parse = new Parse($object);
                    $allow = $parse->compile($allow, $object->data());
                    if($allow){
                        Handler::header('Access-Control-Allow-Origin: ' . $allow);
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
                    [
                        'htm',
                        'html',
                        'php',
                        'xml',
                        'aspx'
                    ]
                )
            ){
                if($config->data('server.http.error.404')){
                    //let's parse this tpl
                    echo 'parser initialisation required...' . PHP_EOL;
                }
            }
            elseif(
                in_array(
                    $extension,
                    [
                        'env',
                        'txt',
                        'log',
                        'cgi',
                    ]
                )
            ){
                if($config->data('server.http.error.404')){
                    echo "HTTP/1.0 404 Not Found";
                }
            }
            elseif(
                in_array(
                    $extension,
                    [
                        'js',
                    ]
                )
            ){
                if($config->data('server.http.error.404')){
                    echo '<script type="text/javascript">
                    console.error("HTTP/1.0 404 Not Found",  "' . $file . '");
                    </script>';
                }
            }
            elseif(
                in_array(
                    $extension,
                    [
                        'json',
                    ]
                )
            ){
                echo '{}';
            }
        }
        exit();
    }

}