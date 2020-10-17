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

function function_file_size_text(Parse $parse, Data $data, $size=0){
    $options = [];
    $options['size'] = $size;
    $object = $parse->object();
    return \Host\Admin\R3m\Io\FileManager\Model\File::size_text(
        $object,
        $options
        );
}
