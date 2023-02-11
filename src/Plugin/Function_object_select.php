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
use R3m\Io\Module\File;
use R3m\Io\Module\Core;

/**
 * @throws \R3m\Io\Exception\ObjectException
 * @throws \R3m\Io\Exception\FileWriteException
 */
function function_object_select(Parse $parse, Data $data, $url, $select=null, $compile=false, $scope='item'){
    return Core::object_select($parse, $data, $url, $select, $compile, $scope);
}
