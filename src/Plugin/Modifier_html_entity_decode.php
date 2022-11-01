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

function modifier_html_entity_decode(Parse $parse, Data $data, $string='', $flags = ENT_QUOTES | ENT_SUBSTITUTE, $encoding=null){
    return html_entity_decode($string, $flags, $encoding);
}