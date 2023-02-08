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

use R3m\Io\Module\Core;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function modifier_uppercase_first_sentence(Parse $parse, Data $data, $value, $delimiter='.'){
    return Core::ucfirst_sentence($value, $delimiter);
}
