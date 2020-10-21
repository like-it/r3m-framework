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

function function_require(Parse $parse, Data $data, $url=''){
    $read = '';
    if(\R3m\Io\Module\File::exist($url)){
        $read = \R3m\Io\Module\File::read($url);
        $mtime = \R3m\Io\Module\File::mtime($url);
        $data->data('r3m.io.parse.view.source.url', $url);
        $parse->storage()->data('r3m.io.parse.view.source.mtime', $mtime);
    } else {
        //below disabled, first time wrong, second time right problem

        d($url);
        $debug = debug_backtrace(true);
        dd($debug);
        $text = 'Require: file not found: ' . $url . ' in template: ' . $data->data('r3m.io.parse.view.source.url');
        throw new Exception($text);

    }
    return $parse->compile($read, [], $data);
}
