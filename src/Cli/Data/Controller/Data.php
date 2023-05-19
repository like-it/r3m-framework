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
use R3m\Io\Exception\ObjectException;
use R3m\Io\Module\Controller;
use R3m\Io\Module\Dir;
use R3m\Io\Module\Event;

use Exception;

use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;

class Data extends Controller {
    const DIR = __DIR__;
    const NAME = 'Data';
    const INFO = '{{binary()}} data                           | Data options';

    /**
     * @throws ObjectException
     */
    public static function run(App $object){
        $submodule = App::parameter($object, 'data', 1);
        switch($submodule){
            case 'backup':
                return Data::backup($object);
            break;
            case 'restore':
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
    public static function backup(App $object){
        $flags = App::flags($object);
        $cwd = false;
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
        if(empty($includes) && empty($excludes)){
            $dir = new Dir();
            $url = $object->config('project.dir.data');
            $read = $dir->read($url);
            $cwd = Dir::change($url);
            if(is_array($read)){
                foreach($read as $nr => $file){
                    if($file->type === Dir::TYPE){
                        ddd($object->config());
//                        $destination = $object->config('')


                    }
                }
            }
            ddd($read);
        }
        if($cwd){
            Dir::change($cwd);
        }


        d($flags);
    }
}