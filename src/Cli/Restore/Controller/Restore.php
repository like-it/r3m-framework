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
use R3m\Io\Config;

use R3m\Io\Exception\ObjectException;
use R3m\Io\Module\Controller;
use R3m\Io\Module\Dir;
use R3m\Io\Module\Event;
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

    /**
     * @throws ObjectException
     * @throws Exception
     */
    public static function run(App $object){
        $id = $object->config(Config::POSIX_ID);
        $filename = $object->parameter($object, Restore::NAME, 1);
        $name = false;
        $url = false;
        if(empty($filename)){
            try {
                $name = Restore::name(Restore::DEFAULT_NAME, Restore::NAME);
                $url = Restore::locate($object, $name);
                $response = Restore::response($object, $url);
                Event::trigger($object, strtolower(Restore::NAME) . '.info', [
                    'name' => $name,
                    'url' => $url,
                ]);
                return $response;
            } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
                Event::trigger($object, strtolower(Restore::NAME) . '.info', [
                    'name' => $name,
                    'url' => $url,
                    'exception' => $exception
                ]);
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
            $dir = $object->config('project.dir.public');
            Dir::create($dir, Dir::CHMOD);
            $destination = $dir . $filename;
            File::copy($source, $destination);
            if(empty($id)){
                exec('chown www-data:www-data ' . $dir);
                exec('chown www-data:www-data ' . $destination);
            }
            if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                exec('chmod 777 ' . $dir);
                exec('chmod 666 ' . $destination);
            } else {
                exec('chmod 640 ' . $destination);
            }
            echo $destination . ' Restored...' . PHP_EOL;
            Event::trigger($object, strtolower(Restore::NAME) . '.' . __FUNCTION__, [
                'url' => $destination,
            ]);
        }
    }
}