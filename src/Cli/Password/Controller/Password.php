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

class Password extends View {
    const DIR = __DIR__;
    const NAME = 'Password';

    public static function run($object){
        $url = Password::locate($object, 'Hash');
        return Password::view($object, $url);
    }
}