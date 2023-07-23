<?php
/**
 * @author          Remco van der Velde
 * @since           19-01-2023
 * @copyright       (c) Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *  -    all
 */
namespace R3m\Io\Module;

use ErrorException;
use Exception;
use R3m\Io\App;
use R3m\Io\Exception\ObjectException;

class SharedMemory {

    /**
     * @throws ObjectException
     */
    public static function read(App $object, $name, $offset=0, $length=0){
        d($name);
        try {
            if(File::exist($name) === false){
                return null;
            }
            $shm_key = ftok($name, 'r');
            $shmop = @shmop_open(
                $shm_key,
                'a',
                0,
                0
            );
            if($length > 0){
                $data = @shmop_read($shmop, $offset, $length);
            } else {
                $data = @shmop_read($shmop, $offset, @shmop_size($shmop));
            }
            if(
                substr($data, 0, 1) === '{' &&
                substr($data, -1, 1) === '}'
            ){
                $data = Core::object($data, Core::OBJECT_OBJECT);
            }
            elseif(
                substr($data, 0, 1) === '[' &&
                substr($data, -1, 1) === ']'
            ){
                $data = Core::object($data, Core::OBJECT_ARRAY);
            }
            elseif($data === 'false'){
                $data = false;
            }
            elseif($data === 'true'){
                $data = true;
            }
            elseif($data === 'null'){
                $data = null;
            }
            elseif(is_numeric($data)){
                $data = $data + 0;
            }
            return $data;
        }
        catch (ErrorException $exception){
            //cache miss
            return null;
        }

    }

    public static function write(App $object, $name, $data='', $permission=File::CHMOD): int
    {
        d($name);
        if(is_array($data) || is_object($data)){
            $data = Core::object($data, Core::OBJECT_JSON_LINE);
        }
        if(!is_string($data)){
            $data = (string) $data;
        }
        if(File::exist($name) === false){
            return false;
        }
        $shm_key = ftok($name, 'r');
        $shm_size = mb_strlen($data);
        $shmop = shmop_open(
            $shm_key,
            'c',
            $permission,
            $shm_size
        );
        return shmop_write($shmop, $data, 0);
    }
}