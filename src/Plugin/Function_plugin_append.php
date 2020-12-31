<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-14
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_plugin_append(Parse $parse, Data $data, $url=null){
    $config = $parse->object()->data(App::CONFIG);
    $plugin = $config->data('parse.dir.plugin');
    $plugin[] = $url;
    $config->data('parse.dir.plugin', $plugin);
    return '';
}
