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

use Exception;
use R3m\Io\App;
use R3m\Io\Config;
use R3m\Io\Module\File;
use R3m\Io\Module\Dir;
use R3m\Io\Module\View;
use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;

class Restore extends View {
    const DIR = __DIR__;
    const NAME = 'Restore';

    const DEFAULT_NAME = 'info';

    const INFO = '{{binary()}} restore                        | Restore system files';

    const FILE = [
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
                return 'Command undefined.' . PHP_EOL;
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
        d(File::exist($source));
        d(in_array($filename, Restore::FILE));
        dd($source);
        if(
            File::exist($source) &&
            in_array($filename, Restore::FILE)
        ){
            $destination = $object->config('project.dir.public') . $filename;
            File::copy($source, $destination);
            $command = 'chown www-data:www-data ' . $destination;
            Core::execute($command);
            echo $destination . ' Restored...' . PHP_EOL;
        }
    }
}