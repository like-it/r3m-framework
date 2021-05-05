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


function function_cors(Parse $parse, Data $data){
    header("HTTP/1.1 200 OK");
    header("Access-Control-Allow-Origin: *");
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    }
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        //header("HTTP/1.1 200 OK");
//        header("Access-Control-Allow-Origin: *");
//        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, Authorization');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
        exit(0);
    }
}
