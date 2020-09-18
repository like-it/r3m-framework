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

function function_r3m_built(Parse $parse, Data $data){
    $config = $parse->object()->data(\R3m\Io\App::CONFIG);
    $built = $config->data(\R3m\Io\Config::DATA_FRAMEWORK_BUILT);
    return $built;

}
