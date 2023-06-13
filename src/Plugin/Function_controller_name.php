<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-13
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Controller;
use R3m\Io\Module\Data;

function function_controller_name(Parse $parse, Data $data, $name=null){
    return Controller::name($name);
}
