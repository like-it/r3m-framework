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

use R3m\Io\Exception\ErrorException;
use stdClass;

use Exception;
use R3m\Io\Exception\FileAppendException;
use R3m\Io\Exception\FileMoveException;
use R3m\Io\Exception\FileWriteException;

class File {
    const CHMOD = 0640;
    const TYPE = 'File';
    const SCHEME_HTTP = 'http';

    public static function is($url=''){
        $url = rtrim($url, '/');
        return is_file($url);
    }

    public static function is_link($url=''){
        return is_link($url);
    }

    public static function is_readable($url=''){
        return is_readable($url);
    }

    public static function is_writeable($url=''){
        return is_writeable($url);
    }

    public static function is_resource($resource=''){
        return is_resource($resource);
    }

    public static function is_upload($url=''){
        return is_uploaded_file($url);
    }

    public static function dir($directory=''){
        return str_replace('\\\/', '/', rtrim($directory,'\\\/')) . '/';
    }

    public static function mtime($url=''){
        try {
            return @filemtime($url); //added @ async deletes & reads can cause triggers otherways
        } catch(Exception $exception){
            return;
        }

    }

    public static function atime($url=''){
        try {
            return @fileatime($url); //added @ async deletes & reads can cause triggers otherways
        } catch (Exception $exception){
            return;
        }
    }

    public static function link($source, $destination){
        $source = escapeshellarg($source);
        $destination = escapeshellarg($destination);
        system('ln -s ' . $source . ' ' . $destination);
        return true;
    }

    public static function count($directory='', $include_directory=false){
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

    public static function exist($url){ //File::exist means File has exist and not exist
        if($url == '/'){
            return file_exists($url);
        } else {
            $url = rtrim($url, '/');
            return file_exists($url);
        }
    }

    public static function touch($url='', $time=null, $atime=null){
        if($atime === null){
            //$exec = 'touch -t' . date('YmdHi.s', $time) . ' ' . $url;
            //$output = [];
            //Core::execute($exec, $output);
            try {
                return @touch($url, $time); //wsdl not working
            } catch (Exception $exception){
                return false;
            }

            //return true;
        } else {
            //$exec = 'touch -t' . date('YmdHi.s', $time) . ' ' . $url;
            //$output = [];
            //Core::execute($exec, $output);
            try {
                return @touch($url, $time, $atime);
            } catch (Exception $exception){
                return false;
            }

            //return true;
        }
    }

    public static function info(stdClass $node){
        $rev = strrev($node->name);
        $explode = explode('.', $rev, 2);
        if(count($explode) == 2){
            $ext = strrev($explode[0]);
            $node->extension = strtolower($ext);
            $node->filetype = ucfirst(strtolower($ext)) . ' ' . strtolower(File::TYPE);
        } else {
            $node->extension = '';
            $node->filetype = File::TYPE;
        }
        $node->mtime = File::mtime($node->url);
        $node->size = File::size($node->url);
        return $node;
    }

    public static function chown($url='', $owner=null, $group=null, $recursive=false){
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

    public static function move($source='', $destination='', $overwrite=false){
        $exist = file_exists($source);
        if($exist === false){
            return new FileMoveException('Source file doesn\'t exist');
        }
        $exist = file_exists($destination);
        if(
            $overwrite === false &&
            file_exists($destination)
        ){
            return new FileMoveException('Destination file exists');
        }
        if(is_dir($source)){
            if(
                $exist &&
                $overwrite === false
            ){
                return new FileMoveException('Destination directory exists');
            }
            elseif($exist){
                if(is_dir($destination)){
                    return new FileMoveException('Destination directory exists and needs to be deleted first');
                } else {
                    try {
                        File::delete($destination);
                        return rename($source, $destination);
                    } catch (Exception  | ErrorException $exception){
                        return $exception;
                    }

                }
            } elseif(
                !$exist &&
                $overwrite === false
            ){
                try {
                    return @rename($source, $destination);
                } catch (Exception | ErrorException $exception){
                    return $exception;
                }

            }
        }
        elseif(is_file($source)){
            return rename($source, $destination);
        }
    }

    public static function chmod($url, $mode=0640){
        return chmod($url, $mode);
    }

    public static function write($url='', $data=''){
        $url = (string) $url;
        $data = (string) $data;
        $fwrite = 0;
        $resource = @fopen($url, 'w');
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
        if(!empty($resource)){
            flock($resource, LOCK_UN);
            fclose($resource);
        }
        if($written != strlen($data)){
            throw new FileWriteException('File.write failed, written != strlen data....');
            return false;
        } else {
            return $written;
        }
    }

    public static function append($url='', $data=''){
        $url = (string) $url;
        $data = (string) $data;
        $fwrite = 0;
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
        if(!empty($resource)){
            flock($resource, LOCK_UN);
            fclose($resource);
        }
        if($written != strlen($data)){
            throw new FileAppendException('File.append failed, written != strlen data....');
            return false;
        } else {
            return $written;
        }
    }

    public static function read($url=''){
        if(strpos($url, File::SCHEME_HTTP) === 0){
            //check network connection first (@) added for that              //error
            try {
                $file = @file($url);
                if(empty($file)){
                    return '';
                }
                return implode('', $file);
            } catch (Exception $exception){
                return '';
            }

        }
        if(empty($url)){
            return '';
        }
        try {
            $file = @file($url);
            if(!empty($file)){
                return implode('', $file);
            }
        } catch (Exception $exception){
            return '';
        }
    }

    public static function copy($source='', $destination=''){
        return copy($source, $destination);
    }

    public static function delete($url=''){
        try {
            return @unlink($url); //added @ async deletes & reads can cause triggers otherways
        } catch (Exception $exception){
            return $exception;
        }

    }

    public static function extension($url=''){        
        if(substr($url, -1) == '/'){
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

    public static function basename($url='', $extension=''){
        if(strstr($url, '\\') !== false){
            $url = str_replace('\\', '/', $url);
        }
        $filename = basename($url);
        $explode = explode('?', $filename, 2);
        $filename = $explode[0];
        $filename = basename($filename, $extension);
        return $filename;
    }

    public static function extension_remove($filename='', $extension=[]){
        if(!is_array($extension)){
            $extension = array($extension);
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

    public static function ucfirst($url=''){
        $explode = explode('.', $url);
        $extension = array_pop($explode);
        $result = '';
        foreach($explode as $part){
            if(empty($part)){
                continue;
            }
            $result .= ucfirst($part) . '.';
        }
        $result .= '.' . $extension;
        return $result;
    }

    public static function size($url=''){
        try {
            return @filesize($url); //pagefile error
        } catch(Exception $exception){
            return 0;
        }
    }

    public static function upload(Data $upload, $target){
        return move_uploaded_file($upload->data('tmp_name'), $target . $upload->data('name'));
    }
}