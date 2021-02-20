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
use stdClass;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\Host;


function function_header(Parse $parse, Data $data, $string='', $http_response_code=null, $replace=true){
	Handler::header($string, $http_response_code, $replace);
}
