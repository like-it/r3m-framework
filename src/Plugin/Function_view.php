<?php
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;


function function_view(Parse $parse, Data $data, $template=null){
    $url = \R3m\Io\Module\View::locate($parse->object());
    $read = \R3m\Io\Module\File::read($url);
    $mtime = \R3m\Io\Module\File::mtime($url);
    $parse->storage()->data('r3m.parse.view.url', $url);
    $parse->storage()->data('r3m.parse.view.mtime', $mtime);

    $read = $parse->compile($read, [], $data);

    return $read;
}
