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

use Exception;
use R3m\Io\App;

class SharedMemory {

    public static function read(App $object, $name, $offset=0, $length=0){
        $shm_key = ftok($name, 'r');
        $shmop = shmop_open(
            $shm_key,
            'a',
            0,
            0
        );
        if($length > 0){
            $data = shmop_read($shmop, $offset, $length);
        } else {
            $data = shmop_read($shmop, $offset, shmop_size($shmop));
        }
        ddd($data);
    }

    public static function write(App $object, $name, $data='', $permission=File::CHMOD): int
    {
        if(is_array($data) || is_object($data)){
            $data = Core::object($data, Core::OBJECT_JSON_LINE);
        }
        if(!is_string($data)){
            $data = (string) $data;
        }
        $shm_key = ftok($name, 'r');
        $shm_size = mb_strlen($data);
        $shmop = shmop_open(
            $shm_key,
            'w',
            $permission,
            $shm_size
        );
        return shmop_write($shmop, $data, 0);
    }
}