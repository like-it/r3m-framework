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

use R3m\Io\Module\View;
use Exception;
use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;

class Password extends View {
    const DIR = __DIR__;
    const NAME = 'Password';
    const INFO = '{binary()} password                       | Password hash generation';
    
    public static function run($object){
        try {
            $name = Password::name('hash', Password::NAME);
            $url = Password::locate($object, $name);
            return Password::response($object, $url);
        } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            return 'Command undefined.' . "\n";
        }
    }
}