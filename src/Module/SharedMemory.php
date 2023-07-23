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
    public static function read(App $object, $url, $offset=0, $length=0){
        try {
            $shmop = @shmop_open(
                1,
                'a',
                0,
                0
            );
            $data = @shmop_read($shmop, $offset, @shmop_size($shmop));
            ddd($data);
            $id = 1000;
            $shmop = @shmop_open(
                $id,
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

    public static function write(App $object, $url, $data='', $permission=File::CHMOD): int
    {
        try {
            if(is_array($data) || is_object($data)){
                $data = Core::object($data, Core::OBJECT_JSON_LINE);
            }
            if(!is_string($data)){
                $data = (string) $data;
            }
            /*
            if(File::exist($name) === false){
                return false;
            }
            $shm_key = ftok($name, 'a'); //seems problematic
            */

            $connect = SharedMemory::read($object, 'mapping');
            if($connect === null){
                $connect = [];
                $id = 1;
                $connect[$id] = 'mapping';
                $id = 1000;
                $connect[$id] = $url;
            } else {
                ddd($connect);
            }
            $shm_size = mb_strlen($data);
            $shmop = @shmop_open(
                $id,
                'c',
                $permission,
                $shm_size
            );
            if($shmop === false){
                $shmop = @shmop_open(
                    $id,
                    'w',
                    $permission,
                    $shm_size
                );
            }
            $write = shmop_write($shmop, $data, 0);
            if($write > 0){
                $data = Core::object($connect, Core::OBJECT_JSON);
                $shm_size = mb_strlen($data);
                $shmop = @shmop_open(
                    $id,
                    'c',
                    $permission,
                    $shm_size
                );
                if($shmop === false){
                    $shmop = @shmop_open(
                        $id,
                        'w',
                        $permission,
                        $shm_size
                    );
                }
                $connect_write = shmop_write($shmop, $data, 0);
                if($connect_write > 0){
                    return $write;
                }
            }
        }
        catch(ErrorException | ObjectException $exception){
            return false;
        }
        return false;
    }
}