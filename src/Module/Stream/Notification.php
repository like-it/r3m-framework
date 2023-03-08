<?php
/**
 * @author          Remco van der Velde
 * @since           19-01-2023
 * @copyright       (c) Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *  -    all
 */
namespace R3m\Io\Module\Stream;

use R3m\Io\App;

class Notification {

    public static function is_new(App $object, $action='', $options=[]){
        d($action);
        ddd($options);

    }
}