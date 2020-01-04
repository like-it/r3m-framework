<?php
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;


function function_section_read(Parse $parse, Data $data, $section=null){
    $template = $parse->object()->data('template');
    if(empty($template)){
        $template = 'Section/' . $section;
    } else {
        $template->name = 'Section/' . $section;
    }
    $url = \R3m\Io\Module\View::locate($parse->object(), $template);
    $read = \R3m\Io\Module\File::read($url);
    $read = $parse->compile($read, [], $data);
    return $read;
}
