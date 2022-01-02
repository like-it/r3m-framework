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
use R3m\Io\Module\Core;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;

function function_require(Parse $parse, Data $data, $url='', $storage=[]){
    if(File::exist($url)){
        $read = File::read($url);
        $mtime = File::mtime($url);
        if(!empty($storage)){
            $data_data = new Data();
            $data_data->data($storage);
            $data_data->data('r3m.io.parse.view.source.url', $url);
            $data_data->data('ldelim', '{');
            $data_data->data('rdelim', '}');
            $parse->storage()->data('r3m.io.parse.view.source.mtime', $mtime);
            $parser = new Parse($parse->object());
            $compile =  $parser->compile($read, [], $data_data);
            $data_script = $data_data->data('script');
            $script = $data->data('script');
            if(!empty($data_script) && empty($script)){
                $data->data('script', $data_script);
            }
            elseif(!empty($data_script && !empty($script))){
                $data->data('script', array_merge($script, $data_script));
            }
            $data_link = $data_data->data('link');
            $link = $data->data('link');
            if(!empty($data_link) && empty($link)){
                $data->data('link', $data_link);
            }
            elseif(!empty($data_link && !empty($link))){
                $data->data('link', array_merge($link, $data_link));
            }
            return $compile;
        } else {
            $source = $data->data('r3m.io.parse.view.source.url');
            $data->data('r3m.io.parse.view.source.url', $url);
            $parse->storage()->data('r3m.io.parse.view.source.mtime', $mtime);
            $parser = new Parse($parse->object());
            $result = $parser->compile($read, [], $data);
            $data->data('r3m.io.parse.view.source.url', $source);
            return $result;
        }
    } else {
        $text = 'Require: file not found: ' . $url . ' in template: ' . $data->data('r3m.io.parse.view.source.url');
        throw new Exception($text);
    }    
}
