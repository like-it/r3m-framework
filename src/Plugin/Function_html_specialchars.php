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
use R3m\Io\Module\Data;
use R3m\Io\Module\Handler;

function function_html_specialchars(Parse $parse, Data $data, $string='', $flags='ENT_COMPAT', $encoding=null, $double_encode=true) {
    if(is_string($flags)){
        $flags = constant($flags);
    }
    return htmlspecialchars($string, $flags, $encoding, $double_encode);
}
