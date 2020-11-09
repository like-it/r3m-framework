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
use Exception;
use R3m\Io\App;
use R3m\Io\Config;

class FileRequest {
    const REQUEST = 'Request';

    public static function get($object){
        $request = $object->data(App::REQUEST);        
        $input = $request->data('request');
        // $input = substr($input, 0, -1);

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
        array_unshift($explode, $controller);
        array_unshift($view, 'Public');
        array_unshift($view, 'View');
        array_unshift($view, $controller);

        $location[] = $config->data('host.dir.root') . implode('/', $view) . $file;
        $location[] = $config->data('host.dir.root') . implode('/', $explode) . $file;
        $location[] = $config->data('host.dir.root') . $dir . 'Public' . $config->data('ds') . $file;
        $location[] = $config->data('host.dir.public') . $dir . $file;
        $location[] = $config->data('project.dir.public') . $dir . $file;
//         $location[] = '/' . $dir . $file;

        foreach($location as $url){
            if(File::exist($url)){
                $host = $object->data('host.url');
                $etag = sha1($url);
                $mtime = File::mtime($url);
                $contentType = $config->data('contentType.' . $extension);
                if(empty($contentType)){
                    Handler::header('HTTP/1.0 415 Unsupported Media Type', 415);
                    exit();
                }
                if(!headers_sent()){
                    $gm = gmdate('D, d M Y H:i:s T', $mtime);
                    Handler::header('Last-Modified: '. $gm);
                    Handler::header('Content-Type: ' . $contentType);
                    Handler::header('ETag: ' . $etag . '-' . $gm);
                    Handler::header('Cache-Control: public');
                    Handler::header('Access-Control-Allow-Origin: http://' . $host);
                }
                return File::read($url);
            }
        }
        Handler::header('HTTP/1.0 404 Not Found', 404);
        d($location);
        exit();
    }

}