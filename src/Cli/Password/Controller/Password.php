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
namespace R3m\Io\Cli\Password\Controller;

use R3m\Io\App;
use R3m\Io\Exception\ObjectException;
use R3m\Io\Module\Controller;

use Exception;

use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;
use R3m\Io\Module\Event;

class Password extends Controller {
    const DIR = __DIR__;
    const NAME = 'Password';
    const INFO = '{{binary()}} password                       | Password hash generation';

    /**
     * @throws ObjectException
     */
    public static function run(App $object){
        $name = false;
        $url = false;
        try {
            $name = Password::name('hash', Password::NAME);
            $url = Password::locate($object, $name);
            $response = Password::response($object, $url);
            Event::trigger($object, 'cli.password.hash', [
                'name' => $name,
                'url' => $url
            ]);
            return $response;
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            Event::trigger($object, 'cli.password.hash', [
                'name' => $name,
                'url' => $url,
                'exception' => $exception
            ]);
            return $exception;
        }
    }
}