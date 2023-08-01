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

function function_view(Parse $parse, Data $data, $template=null, $storage=[]){
    $url = \R3m\Io\Module\Controller::locate($parse->object(), $template);
    $read = \R3m\Io\Module\File::read($url);
    $mtime = \R3m\Io\Module\File::mtime($url);
    if(empty($storage)){
        $data->data('r3m.io.parse.view.source.url', $url);
        $parse->storage()->data('r3m.io.parse.view.source.mtime', $mtime);

        $read = $parse->compile($read, [], $data);
    } else {
        $data_data = new Data();
        $data_data->data($storage);
        $data_data->data('r3m.io.parse.view.source.url', $url);
        $parse->storage()->data('r3m.io.parse.view.source.mtime', $mtime);
        $read = $parse->compile($read, [], $data_data);
        $data_script = $data_data->data('script');
        $script = $data->data('script');
        if(!empty($data_script) && empty($script)){
            $data->data('script', $data_script);
        }
        elseif(!empty($data_script && !empty($script))){
            foreach($script as $nr => $value){
                if(in_array($value, $data_script, true)){
                    unset($script[$nr]);
                }
            }
            $data->data('script', array_merge($script, $data_script));
        }
        $data_link = $data_data->data('link');
        $link = $data->data('link');
        if(!empty($data_link) && empty($link)){
            $data->data('link', $data_link);
        }
        elseif(!empty($data_link && !empty($link))){
            foreach($link as $nr => $value){
                if(in_array($value, $data_link, true)){
                    unset($link[$nr]);
                }
            }
            $data->data('link', array_merge($link, $data_link));
        }
    }
    return $read;
}
