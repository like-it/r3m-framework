<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-13
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;

function function_require(Parse $parse, Data $data, $url=''){    
    if(File::exist($url)){
        $read = File::read($url);
        $mtime = File::mtime($url);
        $data->data('r3m.io.parse.view.source.url', $url);
        $parse->storage()->data('r3m.io.parse.view.source.mtime', $mtime);
        return $parse->compile($read, [], $data);
    } else {
        //below disabled, first time wrong, second time right problem
        $text = 'Require: file not found: ' . $url . ' in template: ' . $data->data('r3m.io.parse.view.source.url');
        throw new Exception($text);
    }    
}
