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
namespace R3m\Io\Cli\Linefeed\Controller;

use R3m\Io\App;
use R3m\Io\Module\Cli;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;
use R3m\Io\Module\View;
use Exception;
use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;

class Linefeed extends View {
    const DIR = __DIR__;
    const NAME = 'Linefeed';
    const INFO = '{{binary()}} linefeed                       | Linefeed';

    /**
     * @throws Exception
     */
    public static function run($object){

        $url = $object->config('controller.dir.data') . 'Linefeed' . $object->config('extension.json');
        $config = $object->data_read($url, sha1($url));
        if($config){
            $directory = App::parameter($object, Linefeed::NAME, 1);
            while(empty($directory)){
                $directory = Cli::read('input', 'Input directory: ');
            }
            if(!Dir::is($directory)){
                throw new Exception('Not a directory.');
            }
            $dir = new Dir();
            $list = $dir->read($directory, true);
            foreach($list as $file){
                $read = File::read($file->url);
                ddd($read);
            }
            ddd($list);
        }
    }
}