<?php

use R3m\Io\App;

use R3m\Io\Module\Event;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;

use R3m\Io\Exception\ObjectException;

/**
 * @throws ObjectException
 */
function function_ln(Parse $parse, Data $data){
    $object = $parse->object();

    $source = App::parameter($object, 'ln', 1);
    $target = App::parameter($object, 'ln', 2);

    if(File::exist($target)){
        $exception = new Exception('File exists...');
        Event::trigger($object, 'cli.ln', [
            'source' => $source,
            'target' => $target,
            'exception' => $exception
        ]);
        return;
    }
    exec('ln -s ' . escapeshellarg($source) . ' ' . escapeshellarg($target));
    Event::trigger($object, 'cli.ln', [
        'source' => $source,
        'target' => $target
    ]);
}
