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
    $url = \R3m\Io\Module\View::locate($parse->object(), $template);
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
    }
    return $read;
}
