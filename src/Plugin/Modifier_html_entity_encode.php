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

function modifier_html_entity_encode(Parse $parse, Data $data, $string='', $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, $encoding=null, $double_encoding=true){
    return htmlentities($string, $flags, $encoding, $double_encoding);
}