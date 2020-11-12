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
use Exception;

class File {
    const CHMOD = 0640;
    const TYPE = 'File';
    const SCHEME_HTTP = 'http';

    public static function is($url=''){
        $url = rtrim($url, '/');
        return is_file($url);
    }

    public static function dir($directory=''){
        return str_replace('\\\/', '/', rtrim($directory,'\\\/')) . '/';
    }

    public static function mtime($url=''){
        return @filemtime($url); //added @ async deletes & reads can cause triggers otherways
    }

    public static function atime($url=''){
        return @fileatime($url); //added @ async deletes & reads can cause triggers otherways
    }

    public static function link($source, $destination){
        system('ln -s ' . $source . ' ' . $destination);
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
            // $exec = 'touch -t' . date('YmdHi.s', $time) . ' ' . $url;
            // echo $exec . "\n";
            return @touch($url, $time); //wsdl not working
        } else {
            return @touch($url, $time, $atime);
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
        $node->mtime = filemtime($node->url);
        $node->size = filesize($node->url);
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
        if($recursive){
            exec('chown ' . $owner . ':' . $group . ' -R ' . $url, $output);
        } else {
            exec('chown ' . $owner . ':' . $group . ' ' . $url, $output);
        }
    }

    public static function move($source='', $destination='', $overwrite=false){
        $exist = file_exists($source);
        if($exist === false){
            throw new Exception('Source file not exists');
        }
        $exist = file_exists($destination);
        if(
            $overwrite === false &&
            file_exists($destination)
        ){
            throw new Exception('Destination file exists');
        }
        if(is_dir($source)){
            if(
                $exist &&
                $overwrite === false
            ){
                throw new Exception('Destination directory exists');
            }
            elseif($exist){
                if(is_dir($destination)){
                    throw new Exception('Destination directory exists and needs to be deleted first');
                } else {
                    File::delete($destination);
                    return rename($source, $destination);
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
            throw new Exception('File.write failed, written != strlen data....');
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
            throw new Exception('File.append failed, written != strlen data....');
            return false;
        } else {
            return $written;
        }
    }

    public static function read($url=''){
        if(strpos($url, File::SCHEME_HTTP) !== false){
            //check network connection first (@) added for that              //error
            $file = @file($url);
            if(!is_array($file)){
                return false;
            }
            return implode('', $file);
        }
        if(empty($url)){
            return '';
        }
        $file = @file($url);
        if(!empty($file)){
            return implode('', $file);
        }
        return '';
    }

    public static function copy($source='', $destination=''){
        return copy($source, $destination);
    }

    public static function delete($url=''){
        return @unlink($url); //added @ async deletes & reads can cause triggers otherways
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

    public static function removeExtension($filename='', $extension=array()){
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

    public static function ucfirst($dir=''){
        $explode = explode('.', $dir);
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
        return @filesize($url); //pagefile error
    }
}