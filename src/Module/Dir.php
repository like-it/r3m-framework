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
use R3m\Io\Exception\ErrorException;

class Dir {
    const CHMOD = 0740;
    const TYPE = 'Dir';
    const SEPARATOR = DIRECTORY_SEPARATOR;
    const FORMAT_FLAT = 'flat';

    private $node;

    public static function change($dir=''){
        $tmp = getcwd() . DIRECTORY_SEPARATOR;

        if(is_dir($dir) === false){
            Dir::create($dir, Dir::CHMOD);
        }
        chdir($dir);
        return $tmp;
    }

    public static function create($url='', $chmod=''){
        $url = rtrim($url, '/');
        if(File::exist($url) && !Dir::is($url)){
            unlink($url);
        }
        if(File::exist($url) && Dir::is($url)){
            return true;
        } else {
            $mkdir = false;
            if(empty($chmod)){
                $mkdir = @mkdir($url, Dir::CHMOD, true);
            } else {
                $mkdir = @mkdir($url, $chmod, true);
            }
            return $mkdir;
        }
    }
    public static function exist($url=''){
        $url = rtrim($url, Dir::SEPARATOR);
        if(
            File::exist($url) === true &&
            Dir::is($url) === true
        ){
            return true;
        }
        return false;
    }
    public static function is($url=''){
        $url = rtrim($url, Dir::SEPARATOR);
        return is_dir($url);
    }

    public static function size($url, $recursive=true){
        if(!Dir::is($url)){
            return false;
        }
        $url = rtrim($url, Dir::SEPARATOR);
        $dir = new Dir();
        $read = $dir->read($url, $recursive, Dir::FORMAT_FLAT);
        $total = 0;
        foreach($read as $file){
            $size = filesize($file->url);
            $total += $size;
        }
        return $total;
    }

    public static function name($url='', $levels=null){
        $is_backslash = false;
        if(stristr($url, '\\') !== false){
            $url = str_replace('\\', '/', $url);
            $is_backslash = true;
        }
        if(is_null($levels)){
            $name = dirname($url);
        } else {
            $levels += 0;
            $name = dirname($url, (int) $levels);
        }
        if($name == '.'){
            return '';
        }
        elseif(substr($name, -1, 1) != '/'){
            $name .= '/';
        }
        if($is_backslash === true){
            $name = str_replace('/', '\\', $name);
        }
        return $name;
    }
    public function ignore($ignore=null, $attribute=null){
        $node = $this->node();
        if(!isset($node)){
            $node = new stdClass();
        }
        if(!isset($node->ignore)){
            $node->ignore = array();
        }
        if($ignore !== null){
            if(is_array($ignore) && $attribute === null){
                $node->ignore = $ignore;
            }
            elseif($ignore == 'delete' && $attribute === null){
                $node->ignore = array();
            }
            elseif($ignore=='list' && $attribute !== null){
                $node->ignore = $attribute;
            }
            elseif($ignore=='find'){
                if(substr($attribute,-1) !== Dir::SEPARATOR){
                    $attribute .= Dir::SEPARATOR;
                }
                foreach ($node->ignore as $item){
                    if(stristr($attribute, $item) !== false){
                        return true;
                    }
                }
                return false;
            } else {
                if(substr($ignore,-1) !== Dir::SEPARATOR){
                    $ignore .= Dir::SEPARATOR;
                }
                $node->ignore[] = $ignore;
            }
        }
        $node = $this->node($node);
        return $node->ignore;
    }
    public function read($url='', $recursive=false, $format='flat'){
        if(substr($url,-1) !== Dir::SEPARATOR){
            $url .= Dir::SEPARATOR;
        }
        if($this->ignore('find', $url)){
            return array();
        }
        $list = array();
        $cwd = getcwd();
        if(is_dir($url) === false){
            return false;
        }
        try {
            @chdir($url);
        } catch (Exception | ErrorException $exception){
            return false;
        }
        try {
            if ($handle = @opendir($url)) {
                while (false !== ($entry = readdir($handle))) {
                    $recursiveList = array();
                    if($entry == '.' || $entry == '..'){
                        continue;
                    }
                    $file = new stdClass();
                    $file->url = $url . $entry;
                    if(is_dir($file->url)){
                        $file->url .= Dir::SEPARATOR;
                        $file->type = Dir::TYPE;
                    }
                    if($this->ignore('find', $file->url)){
                        continue;
                    }
                    $file->name = $entry;
                    if(isset($file->type)){
                        if(!empty($recursive)){
                            $directory = new Dir();
                            $directory->ignore('list', $this->ignore());
                            $recursiveList = $directory->read($file->url, $recursive, $format);
                            if($format !== 'flat'){
                                $file->list = $recursiveList;
                                unset($recursiveList);
                            }
                        }
                    } else {
                        $file->type = File::TYPE;
                    }
                    if(is_link($entry)){
                        $file->link = true;
                    }
                    $list[] = $file;
                    if(!empty($recursiveList)){
                        foreach ($recursiveList as $recursive_file){
                            $list[] = $recursive_file;
                        }
                    }
                }
            }
        } catch (Exception | ErrorException $exception){
             return $exception;
        }
        if(is_resource($handle)){
            closedir($handle);
        }
        @chdir($cwd);
        return $list;
    }
    public static function copy($source='', $target=''){
        exec('cp ' . $source . ' ' . $target . ' -R');
        return true;
    }

    public static function move($source='', $destination='', $overwrite=false){
        try {
            return File::move($source, $destination, $overwrite);
        } catch (Exception | FileMoveException $exception){
            return $exception;
        }

    }

    public static function remove($dir=''){
        if(is_dir($dir) === false){
            return true;
        }
        exec('rm -rf ' . $dir);
        return true;
    }

    public function delete($dir=''){
        if(is_dir($dir) === false){
            return true;
        }
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $nr => $file) {
            if($this->ignore('find', "$dir/$file")){
                continue;
            }
            if(is_dir("$dir/$file")){
                $this->delete("$dir/$file");
            } else {
                unlink("$dir/$file");
                unset($files[$nr]);
            }
        }
        if($this->ignore('find', "$dir")){
            return true;
        }
        return rmdir($dir);
    }
    public function node($node=null){
        if($node !== null){
            $this->setNode($node);
        }
        return $this->getNode();
    }
    private function setNode($node=null){
        $this->node = $node;
    }
    private function getNode(){
        return $this->node;
    }

    public static function ucfirst($dir=''){
        $explode = explode('/', $dir);
        $result = '';
        foreach($explode as $part){
            if(empty($part)){
                continue;
            }
            $result .= ucfirst($part) . '/';
        }
        return $result;
    }
}