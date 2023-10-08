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
namespace R3m\Io\Module;


use stdClass;

use R3m\Io\App;

use Exception;
use R3m\Io\Exception\ErrorException;
use R3m\Io\Exception\FileAppendException;
use R3m\Io\Exception\FileMoveException;
use R3m\Io\Exception\FileWriteException;

class File {
    const CHMOD = 0640;
    const TYPE = 'File';
    const SCHEME_HTTP = 'http';

    const USER_WWW = 'www-data';

    const STRING = 'string';
    const ARRAY = 'array';

    const SIZE = 'size';
    const BYTE = 'byte';
    const BYTES = 'bytes';
    const LINE = 'line';
    const LINES = 'lines';

    public static function is($url=''): bool
    {
        $url = rtrim($url, '/');
        return is_file($url);
    }

    public static function is_link($url=''): bool
    {
        $url = rtrim($url, '/');
        return is_link($url);
    }

    public static function is_readable($url=''): bool
    {
        $url = rtrim($url, '/');
        return is_readable($url);
    }

    public static function is_writeable($url=''): bool
    {
        $url = rtrim($url, '/');
        return is_writeable($url);
    }

    public static function is_resource($url=''): bool
    {
        $url = rtrim($url, '/');
        return is_resource($url);
    }

    public static function is_upload($url=''): bool
    {
        $url = rtrim($url, '/');
        return is_uploaded_file($url);
    }

    public static function dir($directory=''): string
    {
        return str_replace('\\\/', '/', rtrim($directory,'\\\/')) . '/';
    }

    public static function mtime($url=''){
        try {
            return @filemtime($url); //added @ async deletes & reads can cause triggers otherways
        } catch(Exception $exception){
            return null;
        }

    }

    public static function atime($url=''){
        try {
            return @fileatime($url); //added @ async deletes & reads can cause triggers otherways
        } catch (Exception $exception){
            return null;
        }
    }

    public static function link($source, $destination): bool
    {
        if(substr($source, -1, 1) === '/'){
            $source = substr($source, 0, -1);
        }
        if(substr($destination, -1, 1) === '/'){
            $destination = substr($destination, 0, -1);
        }
        $source = escapeshellarg($source);
        $destination = escapeshellarg($destination);
        system('ln -s ' . $source . ' ' . $destination);
        return true;
    }

    public static function readlink($url, $final=false): string
    {
        $url = escapeshellarg($url);
        if($final){
            $output = system('readlink -f ' . $url);
        } else {
            $output = system('readlink ' . $url);
        }
        return $output;
    }

    public static function count($directory='', $include_directory=false): int
    {
        $dir = new Dir();
        $read = $dir->read($directory);
        if(!empty($include_directory)){
            return count($read);
        } else {
            $count = 0;
            foreach($read as $file){
                if(!property_exists($file, 'type')){
                    continue;
                }
                if($file->type == File::TYPE){
                    $count++;
                }
            }
            return $count;
        }
    }

    public static function exist($url): bool
    {
        if($url == '/'){
            return file_exists($url);
        } else {
            if(is_object($url)){
                $debug = debug_backtrace(true);
                d($debug[0]['file'] . ':' . $debug[0]['line']);
                d($debug[1]['file'] . ':' . $debug[1]['line']);
                ddd($url);
            }
            $url = rtrim($url, '/');
            return file_exists($url);
        }
    }

    public static function touch($url='', $time=null, $atime=null): bool
    {
        if($time === null){
            $time = time();
        }
        if($atime === null){
            try {
                return @touch($url, $time); //wsdl not working
            } catch (Exception $exception){
                return false;
            }
        } else {
            try {
                return @touch($url, $time, $atime);
            } catch (Exception $exception){
                return false;
            }
        }
    }

    public static function info(App $object, stdClass $node): stdClass
    {
        $rev = strrev($node->name);
        $explode = explode('.', $rev, 2);
        if(count($explode) == 2){
            $ext = strrev($explode[0]);
            $node->extension = $ext;
            $node->filetype = ucfirst($ext) . ' ' . strtolower(File::TYPE);
            $node->contentType = $object->config('contentType.' . $ext);
        } else {
            $node->extension = '';
            if($node->type === Dir::TYPE){
                $node->filetype = Dir::TYPE;
            } else {
                $node->filetype = File::TYPE;
            }
        }
        $node->mtime = File::mtime($node->url);
        $node->size = File::size($node->url);
        return $node;
    }

    public static function chown($url='', $owner=null, $group=null, $recursive=false): bool
    {
        if($owner === null){
            $owner = 'root:root';
        }
        if($group == null){
            $explode = explode(':', $owner, 2);
            if(count($explode) == 1){
                $group = $owner;
            } else {
                $owner = $explode[0];
                $group = $explode[1];
            }
        }
        $output = [];
        $owner = escapeshellarg($owner);
        $group = escapeshellarg($group);
        $url = escapeshellarg($url);
        if($recursive){
            exec('chown ' . $owner . ':' . $group . ' -R ' . $url, $output);
        } else {
            exec('chown ' . $owner . ':' . $group . ' ' . $url, $output);
        }
        return true;
    }

    /**
     * @throws FileMoveException
     */
    public static function move($source='', $destination='', $overwrite=false): bool
    {
        if(substr($source, -1, 1) === DIRECTORY_SEPARATOR){
            $source = substr($source, 0, -1);
        }
        if(substr($destination, -1, 1) === DIRECTORY_SEPARATOR){
            $destination = substr($destination, 0, -1);
        }
        if(
            $overwrite &&
            File::exist($destination)
        ){
            if(File::is_link($destination)){
                File::remove($destination);
            }
            elseif(Dir::is($destination)){
//              continue overwrite
            } else {
                File::remove($destination);
            }
            $source = escapeshellarg($source);
            $destination = escapeshellarg($destination);
            exec('mv ' . $source . ' ' . $destination);
            return true;
        } elseif(
            !$overwrite &&
            File::exist($destination)
        ){
            throw new FileMoveException('Destination file already exists...');
        } else {
            $source = escapeshellarg($source);
            $destination = escapeshellarg($destination);
            exec('mv ' . $source . ' ' . $destination);
            return true;
        }
    }

    /**
     * @throws FileMoveException
     */
    public static function rename($source='', $destination='', $overwrite=false): bool
    {
        if(substr($source, -1, 1) === DIRECTORY_SEPARATOR){
            $source = substr($source, 0, -1);
        }
        if(substr($destination, -1, 1) === DIRECTORY_SEPARATOR){
            $destination = substr($destination, 0, -1);
        }
        $exist = File::exist($source);
        if($exist === false){
            throw new FileMoveException('Source file doesn\'t exist...');
        }
        $exist = File::exist($destination);
        if(
            $overwrite === false &&
            File::exist($destination)
        ){
            throw new FileMoveException('Destination file already exists...');
        }
        if(Dir::is($source)){
            if(
                $exist &&
                $overwrite === false
            ){
                throw new FileMoveException('Destination directory exists...');
            }
            elseif($exist){
                if(Dir::is($destination)){
                    throw new FileMoveException('Destination directory exists and needs to be deleted first...');
                } else {
                    try {
                        File::delete($destination);
                        return @rename($source, $destination);
                    } catch (Exception  | ErrorException $exception){
                        return false;
                    }

                }
            } elseif(
                !$exist &&
                $overwrite === false
            ){
                try {
                    return @rename($source, $destination);
                } catch (Exception | ErrorException $exception){
                    return false;
                }

            }
        }
        elseif(File::is($source)){
            try {
                return @rename($source, $destination);
            } catch (Exception | ErrorException $exception){
                return false;
            }
        }
        return false;
    }

    public static function chmod($url, $mode=0640): bool
    {
        return chmod($url, $mode);
    }


    public static function put($url, $data, $flags=LOCK_EX, $return='size'): bool|int
    {
        $size = file_put_contents($url, $data, $flags);
        switch($return){
            case File::SIZE:
            case File::BYTE:
            case File::BYTES:
                return $size;
            case File::LINE:
            case File::LINES:
                $explode = explode(PHP_EOL, $data);
                return $size !== false ? count($explode) : false;
            default:
                return $size;
        }
    }

    /**
     * @throws FileWriteException
     * @bug may write wrong files in Parse:Build:write in a multithreading situation . solution use file::put
     */
    public static function write($url='', $data='', $return='size'){
        $url = (string) $url;
        $data = (string) $data;
        return File::put($url, $data, LOCK_EX, $return);
    }

    /**
     * @throws FileAppendException
     */
    public static function append($url='', $data=''){
        $url = (string) $url;
        $data = (string) $data;
        $resource = @fopen($url, 'a');
        if($resource === false){
            return $resource;
        }        
        flock($resource, LOCK_EX);
        for ($written = 0; $written < strlen($data); $written += $fwrite) {
            $fwrite = fwrite($resource, substr($data, $written));
            if ($fwrite === false) {
                break;
            }
        }
        flock($resource, LOCK_UN);
        fclose($resource);
        if($written != strlen($data)){
            throw new FileAppendException('File.append failed, written != strlen data....');
        } else {
            return $written;
        }
    }

    public static function read($url='', $return=File::STRING) : string | array
    {
        if(strpos($url, File::SCHEME_HTTP) === 0){
            //check network connection first (@) added for that              //error
            try {
                $file = @file($url);
                switch($return){
                    case File::ARRAY:
                        if(empty($file)){
                            return [];
                        }
                        return $file;
                    default:
                        if(empty($file)){
                            return '';
                        }
                        return implode('', $file);
                }

            } catch (Exception $exception){
                switch($return){
                    case File::ARRAY:
                        return [];
                    default:
                        return '';
                }
            }
        }
        if(empty($url)){
            switch($return){
                case File::ARRAY:
                    return [];
                default:
                    return '';
            }
        }
        try {
            switch($return){
                case File::ARRAY:
                    return file($url);
                default:
                    return file_get_contents($url);
            }

        } catch (Exception $exception){
            switch($return){
                case File::ARRAY:
                    return [];
                default:
                    return '';
            }
        }
    }

    public static function tail($url, $include_return=false) : string
    {
        if(File::exist($url)){
            $read = File::read($url);
            $data = explode("\r", $read);
            if($include_return === true){
                return end($data) . "\r";
            } else {
                return end($data);
            }

        }
        return '';
    }

    /**
     * @throws Exception
     */
    public static function copy($source='', $destination=''): bool
    {
        try {
            return copy($source, $destination);
        }
        catch(\ErrorException $exception){
            throw new Exception ('Couldn\'t copy source (' . $source . ') to destination (' . $destination .').');
        }
    }


    public static function remove($url=''): bool
    {
        $url = escapeshellarg($url);
        exec('rm  ' . $url);
        return true;
    }

    public static function delete($url=''): bool
    {
        try {
            $url = rtrim($url, '/');
            return @unlink($url); //added @ async deletes & reads can cause triggers otherways
        } catch (Exception $exception){
            return false;
        }

    }

    public static function extension($url=''): string
    {
        if(substr($url, -1) === '/'){
            return '';
        }
        $url = basename($url);
        $ext = explode('.', $url);
        if(!isset($ext[1])){
            $extension = '';
        } else {
            $extension = array_pop($ext);
        }
        return $extension;
    }

    public static function basename($url='', $extension=''): string
    {
        if(strstr($url, '\\') !== false){
            $url = str_replace('\\', '/', $url);
        }
        $filename = basename($url);
        $explode = explode('?', $filename, 2);
        $filename = $explode[0];
        $filename = str_replace(
            [
                ':',
                '='
            ],
            [
                '.',
                '-'
            ],
            $filename
        );
        $filename = basename($filename, $extension);
        return $filename;
    }

    public static function extension_remove($filename='', $extension=[]): string
    {
        if(!is_array($extension)){
            $extension = [($extension)];
        }
        foreach($extension as $ext){
            $ext = '.' . ltrim($ext, '.');
            $filename = explode($ext, $filename, 2);
            if(count($filename) > 1 && empty(end($filename))){
                array_pop($filename);
            }
            $filename = implode($ext, $filename);
        }
        return $filename;
    }

    public static function ucfirst($url=''): string
    {
        $explode = explode('.', $url);
        $extension = null;
        if(array_key_exists(1, $explode)){
            $extension = array_pop($explode);
            $result = '';
            foreach($explode as $part){
                if(empty($part)){
                    continue;
                }
                $result .= ucfirst($part) . '.';
            }
        } else {
            $result = $explode[0];
        }
        if($extension){
            $result .= $extension;
        }
        return $result;
    }

    public static function size($url=''): int
    {
        try {
            return @filesize($url); //pagefile error
        } catch(Exception $exception){
            return 0;
        }
    }

    public static function upload(Data $upload, $target): bool
    {
        return move_uploaded_file($upload->data('tmp_name'), $target . $upload->data('name'));
    }
}
