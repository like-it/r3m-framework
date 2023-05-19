<?php
/**
 * @author          Remco van der Velde
 * @since           04-01-2019
 * @copyright       (c) Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *  -    all
 */
namespace R3m\Io\Cli\Init\Controller;

use R3m\Io\App;
use R3m\Io\Exception\ObjectException;
use R3m\Io\Module\Controller;
use R3m\Io\Module\Event;

use Exception;

use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;

class Data extends Controller {
    const DIR = __DIR__;
    const NAME = 'Init';
    const INFO = '{{binary()}} data                           | Data options';

    /**
     * @throws ObjectException
     */
    public static function run(App $object){
        ddd('data');
        Event::trigger($object, 'cli.init.run', [
            'flags' => $object->flags($object),
            'options' => $object->options($object)
        ]);
        return null;
    }
}