<?php
/**
 * @author         Remco van der Velde
 * @since         2016-10-19
 * @version        1.0
 * @changeLog
 *     -    all
 */
namespace R3m\Io\Cli\Password\Controller;

use R3m\Io\Module\View;

class Password extends View {
    const DIR = __DIR__;
    const NAME = 'Password';

    public static function run($object){
        $url = Password::locate($object, 'Info');
        return Password::view($object, $url);
    }
}