<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *  -    all
 */

namespace R3m\Io\Module\Parse;

use Exception;
use R3m\Io\Module\Data;

class Code {

    public static function result(Build $build, Data $storage, $type='', $selection=[]){

        $result = '';
        switch($type){
            case Build::VARIABLE_ASSIGN :
                $result = Variable::assign($build, $storage, $selection, true);
            break;
            default:
                throw new Exception('type not defined, (' . $type .')');
        }
        dd($result);
        return $result;
    }
}