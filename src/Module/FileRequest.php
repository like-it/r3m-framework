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
        $request = $object->data(App::DATA_REQUEST);

        $input = $request->data('request');
        $input = substr($input, 0, -1);

        $dir = Dir::name($input);
        $file = str_replace($dir,'', $input);
        $extension = File::extension($file);

        if(empty($extension)){
            return false;
        }
        $config = $object->data(App::DATA_CONFIG);

        $location = [];
        $location[] = $config->data('host.dir.public') . $dir . $file;
        $location[] = $config->data('project.dir.public') . $dir . $file;

        foreach($location as $url){
            if(File::exist($url)){
                $host = $object->data('host.url');
                $etag = sha1($url);
                $mtime = File::mtime($url);
                $contentType = $config->data('contentType.' . $extension);
                if(empty($contentType)){
                    Handler::response_header('HTTP/1.0 415 Unsupported Media Type', 415);
                    exit();
                }
                if(!headers_sent()){
                    $gm = gmdate('D, d M Y H:i:s T', $mtime);
                    Handler::response_header('Last-Modified: '. $gm);
                    Handler::response_header('Content-Type: ' . $contentType);
                    Handler::response_header('ETag: ' . $etag . '-' . $gm);
                    Handler::response_header('Cache-Control: public');
                    Handler::response_header('Access-Control-Allow-Origin: http://' . $host);
                }
                return File::read($url);
            }
        }
        Handler::response_header('HTTP/1.0 404 Not Found', 404);
        exit();
    }

}