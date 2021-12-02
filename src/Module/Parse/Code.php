<?php
/**
 * @author          Remco van der Velde
 * @since           04-01-2019
 * @copyright       (c) Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *  -    all
 */
namespace R3m\Io\Module\Parse;

use Exception;
use R3m\Io\Module\Data;

class Code {

    /**
     * @throws Exception
     */
    public static function result(Build $build, Data $storage, $type='', $selection=[]){
        $result = '';
        switch($type){
            case Build::VARIABLE_ASSIGN :
                $result = Variable::assign($build, $storage, $selection, true);
            break;
            case Build::VARIABLE_DEFINE :
                $result = Variable::define($build, $storage, $selection);
            break;
            case Build::METHOD_CONTROL :
                $result = Method::create_control($build, $storage, $selection);
            break;
            case '' :
                if(empty($selection)){
                    return;
                } else {
                    dd($selection);
                }
            default:
                d($selection);
                throw new Exception('type not defined, (' . $type .')');
        }
        return $result;
    }
}