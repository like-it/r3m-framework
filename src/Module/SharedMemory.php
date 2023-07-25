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
        $data = null;
        $connect = null;
        try {
            $connect_shmop = @shmop_open(
                1,
                'a',
                0,
                0
            );
            $connect = @shmop_read($connect_shmop, 0, @shmop_size($connect_shmop));
            $connect = explode("\0", $connect, 2);
            $connect = Core::object($connect[0], Core::OBJECT_ARRAY);
        }
        catch (ErrorException $exception) {
            //no mapping
        }
        $id = false;
        if(is_array($connect)){
            //make binary search ?
            foreach($connect as $nr => $record){
                if($record === $url){
                    $id = $nr;
                    break;
                }
            }
        }
        if($id === false){
            return null;
        }
        try {
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
            $data = explode("\0", $data, 2);
            $data = $data[0];
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
            //ftok goes wrong on linux with url
            $connect = SharedMemory::read($object, 'mapping');
            if($connect === null){
                $connect = [];
                $id = 1;
                $connect[$id] = 'mapping';
                $id = 1000;
                $connect[$id] = $url;
            } else {
                $connect = Core::object($connect, Core::OBJECT_ARRAY);
                if(!is_array($connect)){
                    $connect = [];
                    $id = 1;
                    $connect[$id] = 'mapping';
                    $id = 1000;
                    $connect[$id] = $url;
                } else {
                    $id = array_key_last($connect) + 1;
                    $connect[$id] = $url;
                }
            }
            $data .= "\0";
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
                $connect = Core::object($connect, Core::OBJECT_JSON);
                $connect .= "\0";
                $shm_size = mb_strlen($connect);
                $connect_shmop = @shmop_open(
                    1,
                    'c',
                    $permission,
                    $shm_size
                );
                if($connect_shmop === false){
                    $connect_shmop = @shmop_open(
                        1,
                        'w',
                        $permission,
                        $shm_size
                    );
                }
                $connect_write = shmop_write($connect_shmop, $connect, 0);
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