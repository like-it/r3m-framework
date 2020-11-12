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
namespace R3m\Io\Cli\Uuid\Controller;

use R3m\Io\Module\View;

class Uuid extends View {
    const DIR = __DIR__;
    const NAME = 'Uuid';

    public static function run($object){
        $url = Uuid::locate($object, 'Uuid');
        return Uuid::view($object, $url);
    }
}