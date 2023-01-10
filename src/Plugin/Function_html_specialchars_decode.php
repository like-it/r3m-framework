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

function function_html_specialchars_decode(Parse $parse, Data $data, $string='', $flags='ENT_COMPAT') {
    if(is_string($flags)){
        $flags = constant($flags);
    }
    return htmlspecialchars_decode($string, $flags);
}
