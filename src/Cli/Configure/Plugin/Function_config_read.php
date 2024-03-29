<?php

use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Event;

use R3m\Io\Exception\ObjectException;

/**
 * @throws ObjectException
 */
function function_config_read(Parse $parse, Data $data, $attribute=''){
    $object = $parse->object();
    $url = $object->config('app.config.url');
    $read = $object->data_read($url);
    $response = null;
    if(!empty($read)){
        $response = $read->get($attribute);
    }
    Event::trigger($object, 'cli.configure.config.read', [
        'attribute' => $attribute
    ]);
    return $response;
}

