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
namespace R3m\Io\Cli\Restore\Controller;


use R3m\Io\App;
use R3m\Io\Module\Controller;
use R3m\Io\Module\File;
use R3m\Io\Module\Core;

use Exception;

use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;

class Restore extends Controller {
    const DIR = __DIR__;
    const NAME = 'Restore';

    const DEFAULT_NAME = 'info';

    const INFO = '{{binary()}} restore                        | Restore system files';

    const FILE = [
        '.user.ini',
        '.htaccess',
        'index.php'
    ];

    public static function run(App $object){
        $filename = $object->parameter($object, Restore::NAME, 1);
        if(empty($filename)){
            try {
                $name = Restore::name(Restore::DEFAULT_NAME, Restore::NAME);
                $url = Restore::locate($object, $name);
                return Restore::response($object, $url);
            } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
                return $exception;
            }
        }
        $dir =
            $object->config('framework.dir.cli') .
            'Configure' .
            $object->config('ds') .
            $object->config('dictionary.data') .
            $object->config('ds')
        ;
        $source = $dir . $filename;
        if(
            File::exist($source) &&
            in_array($filename, Restore::FILE)
        ){
            $destination = $object->config('project.dir.public') . $filename;
            File::copy($source, $destination);
            $command = 'chown www-data:www-data ' . $destination;
            Core::execute($object, $command);
            echo $destination . ' Restored...' . PHP_EOL;
        }
    }
}