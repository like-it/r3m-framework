<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_file_exist(Parse $parse, Data $data, $url=''){
    return \R3m\Io\Module\File::exist($url);
}
