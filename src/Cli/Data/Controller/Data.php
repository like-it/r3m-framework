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
namespace R3m\Io\Cli\Data\Controller;

use R3m\Io\App;
use R3m\Io\Config;
use R3m\Io\Exception\ObjectException;
use R3m\Io\Module\Controller;
use R3m\Io\Module\Core;
use R3m\Io\Module\Dir;
use R3m\Io\Module\Event;

use Exception;

use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;
use R3m\Io\Module\File;
use R3m\Io\Module\Sort;

class Data extends Controller {
    const DIR = __DIR__;
    const NAME = 'Data';
    const INFO = '{{binary()}} data                           | Data options';

    /**
     * @throws ObjectException
     */
    public static function run(App $object): void
    {
        $submodule = App::parameter($object, 'data', 1);
        switch($submodule){
            case 'backup':
                Data::backup($object);
            break;
            case 'restore':
                Data::restore($object);
            break;
            case 'download':
                //rsync
            break;
            case 'upload':
                //rsync
            break;
        }
    }

    /**
     * @throws ObjectException
     */
    public static function restore(App $object): void
    {
        $dir = new Dir();
        $url = $object->config('project.dir.backup') . 'Data' . $object->config('ds');
        $read = $dir->read($url);
        $record = false;
        $flags = App::flags($object);
        if(is_array($read)){
            $read = Sort::list($read)->with(['name' => 'desc']);
            if(property_exists($flags, 'date')){
                foreach($read as $nr => $file){
                    if($file->name === $flags->date){
                        $record = $file;
                        break;
                    }
                }
            } else {
                foreach($read as $record){
                    break;
                }
            }
        }
        $includes = [];
        $excludes = [];
        if(property_exists($flags, 'include')){
            $includes = explode(',', $flags->include);
            foreach($includes as $nr => $include){
                $includes[$nr] = trim($include);
            }
        }
        if(property_exists($flags, 'exclude')){
            $excludes = explode(',', $flags->exclude);
            foreach($excludes as $nr => $exclude){
                $excludes[$nr] = trim($exclude);
            }
        }
        if($record){
            $read = $dir->read($record->url);
            if(is_array($read)){
                foreach($read as $file){
                    if($file->type === File::TYPE){
                        $file->extension = File::extension($file->name);
                        $file->basename = File::basename($file->name, '.' . $file->extension);
                        if(empty($includes) && empty($excludes)){
                            if($file->extension === 'zip'){
                                $dir_data = $object->config('project.dir.data') .
                                    File::basename($file->name, $object->config('extension.zip')) .
                                    $object->config('ds')
                                ;
                                Dir::create($dir_data, Dir::CHMOD);
                                $command = Core::binary() . ' zip extract ' . $file->url . ' /';

                                echo $command . PHP_EOL;
                                exec($command);
                                $command = 'chown www-data:www-data ' . $dir_data . ' -R';
                                exec($command);
                                if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                                    $data = $dir->read($dir_data, true);
                                    if(is_array($data)){
                                        foreach($data as $item){
                                            if($item->type === Dir::TYPE){
                                                $command = 'chmod 777 ' . $item->url;
                                                exec($command);
                                            }
                                            elseif($item->type === File::TYPE){
                                                $command = 'chmod 666 ' . $item->url;
                                                exec($command);
                                            }
                                        }
                                    }
                                    $command = 'chmod 777 ' . $dir_data;
                                    exec($command);
                                }
                            }
                        }
                        elseif(!empty($includes) && empty($excludes)){
                            if(
                                in_array(
                                    $file->basename,
                                    $includes,
                                    true
                                ) &&
                                $file->extension === 'zip'
                            ){
                                $command = Core::binary() . ' zip extract ' . $file->url . ' /';
                                $dir_data = $object->config('project.dir.data') .
                                    File::basename($file->name, $object->config('extension.zip')) .
                                    $object->config('ds')
                                ;
                                exec($command);
                                $command = 'chown www-data:www-data ' . $dir_data . ' -R';
                                exec($command);
                                if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                                    $data = $dir->read($dir_data, true);
                                    if(is_array($data)){
                                        foreach($data as $item){
                                            if($item->type === Dir::TYPE){
                                                $command = 'chmod 777 ' . $item->url;
                                                exec($command);
                                            }
                                            elseif($item->type === File::TYPE){
                                                $command = 'chmod 666 ' . $item->url;
                                                exec($command);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        elseif(empty($includes) && !empty($excludes)){
                            if(
                                in_array(
                                    $file->basename,
                                    $excludes,
                                    true
                                )
                            ){
                                continue;
                            }
                            if($file->extension === 'zip'){
                                $command = Core::binary() . ' zip extract ' . $file->url . ' /';
                                $dir_data = $object->config('project.dir.data') .
                                    File::basename($file->name, $object->config('extension.zip')) .
                                    $object->config('ds')
                                ;
                                exec($command);
                                $command = 'chown www-data:www-data ' . $dir_data . ' -R';
                                exec($command);
                                if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                                    $data = $dir->read($dir_data, true);
                                    if(is_array($data)){
                                        foreach($data as $item){
                                            if($item->type === Dir::TYPE){
                                                $command = 'chmod 777 ' . $item->url;
                                                exec($command);
                                            }
                                            elseif($item->type === File::TYPE){
                                                $command = 'chmod 666 ' . $item->url;
                                                exec($command);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        elseif(!empty($includes) && !empty($excludes)){
                            if(
                                in_array(
                                    $file->basename,
                                    $excludes,
                                    true
                                )
                            ){
                                continue;
                            }
                            if(
                                in_array(
                                    $file->basename,
                                    $includes,
                                    true
                                ) &&
                                $file->extension === 'zip'
                            ){
                                $command = Core::binary() . ' zip extract ' . $file->url . ' /';
                                $dir_data = $object->config('project.dir.data') .
                                    File::basename($file->name, $object->config('extension.zip')) .
                                    $object->config('ds')
                                ;
                                exec($command);
                                $command = 'chown www-data:www-data ' . $dir_data . ' -R';
                                exec($command);
                                if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                                    $data = $dir->read($dir_data, true);
                                    if(is_array($data)){
                                        foreach($data as $item){
                                            if($item->type === Dir::TYPE){
                                                $command = 'chmod 777 ' . $item->url;
                                                exec($command);
                                            }
                                            elseif($item->type === File::TYPE){
                                                $command = 'chmod 666 ' . $item->url;
                                                exec($command);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @throws ObjectException
     */
    public static function backup(App $object): void
    {
        $flags = App::flags($object);
        $cwd = false;
        $date = date('Y-m-d-H-i-s');
        $destination_dir = $object->config('project.dir.backup') .
            'Data' .
            $object->config('ds') .
            $date .
            $object->config('ds')
        ;
        $includes = [];
        $excludes = [];
        if(property_exists($flags, 'include')){
            $includes = explode(',', $flags->include);
            foreach($includes as $nr => $include){
                $includes[$nr] = trim($include);
            }
        }
        if(property_exists($flags, 'exclude')){
            $excludes = explode(',', $flags->exclude);
            foreach($excludes as $nr => $exclude){
                $excludes[$nr] = trim($exclude);
            }
        }
        $dir = new Dir();
        $url = $object->config('project.dir.data');
        $read = $dir->read($url);
        $cwd = Dir::change($url);
        if(is_array($read)){
            foreach($read as $nr => $file){
                if(
                    !empty($includes) &&
                    empty($excludes) &&
                    in_array(
                        strtolower($file->name),
                        $includes,
                        true
                    )
                ){
                    if($file->type === Dir::TYPE){
                        $destination_url = $destination_dir .
                            $file->name .
                            $object->config('extension.zip')
                        ;
                        Dir::create($destination_dir, Dir::CHMOD);
                        $command = Core::binary() . ' zip archive ' . $file->url . ' ' . $destination_url;
                        exec($command);
                    }
                }
                elseif(empty($includes) && !empty($excludes)){
                    if(in_array(
                        strtolower($file->name),
                        $excludes,
                        true
                    )){
                        continue;
                    }
                    if($file->type === Dir::TYPE){
                        $destination_url = $destination_dir .
                            $file->name .
                            $object->config('extension.zip')
                        ;
                        Dir::create($destination_dir, Dir::CHMOD);
                        $command = Core::binary() . ' zip archive ' . $file->url . ' ' . $destination_url;
                        exec($command);
                    }
                }
                elseif(!empty($includes) && !empty($excludes)){
                    if(in_array(
                        strtolower($file->name),
                        $excludes,
                        true
                    )){
                        continue;
                    }
                    if(in_array(
                        strtolower($file->name),
                        $includes,
                        true
                    )){
                        if($file->type === Dir::TYPE){
                            $destination_url = $destination_dir .
                                $file->name .
                                $object->config('extension.zip')
                            ;
                            Dir::create($destination_dir, Dir::CHMOD);
                            $command = Core::binary() . ' zip archive ' . $file->url . ' ' . $destination_url;
                            exec($command);
                        }
                    }
                }
                elseif(empty($includes) && empty($excludes)) {
                    if($file->type === Dir::TYPE){
                        $destination_url = $destination_dir .
                            $file->name .
                            $object->config('extension.zip')
                        ;
                        Dir::create($destination_dir, Dir::CHMOD);
                        $command = Core::binary() . ' zip archive ' . $file->url . ' ' . $destination_url;
                        exec($command);
                    }
                }
            }
        }
        if($cwd){
            Dir::change($cwd);
        }
    }
}