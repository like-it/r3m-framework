<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_r3m_built(Parse $parse, Data $data){
    $config = $parse->object()->data(\R3m\Io\App::CONFIG);
    $built = $config->data(\R3m\Io\Config::DATA_FRAMEWORK_BUILT);
    return $built;

}
