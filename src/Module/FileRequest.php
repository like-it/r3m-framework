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
        
        $request = $object->data(App::NAMESPACE . '.' . FileRequest::REQUEST);
        
        $input = substr($request->Input->request, 0, -1);
        $dir = Dir::name($input);
        $file = str_replace($dir,'', $input);
        $extension = File::extension($file);
        
        if(empty($extension)){
            return false;
        }
        $config = $object->data(App::NAMESPACE . '.' . Config::NAME);
        
        $location = [];
        $location[] = $config->data('host.dir.public') . $dir . $file;
        $location[] = $config->data('project.dir.public') . $dir . $file;
        
        foreach($location as $url){
            if(File::exist($url)){
                return File::read($url);
            }
        }
        Handler::response_header('HTTP/1.0 404 Not Found', 404);
        exit();
    }
    
}