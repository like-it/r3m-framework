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
use R3m\Io\Module\Controller;

use Exception;

class Linefeed extends Controller {
    const DIR = __DIR__;
    const NAME = 'Linefeed';
    const INFO = '{{binary()}} linefeed                       | Linefeed';

    /**
     * @throws Exception
     */
    public static function run($object): string
    {
        $url = $object->config('controller.dir.data') . 'Linefeed' . $object->config('extension.json');
        $config = $object->data_read($url, sha1($url));
        $counter = 0;
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
                $extension = File::extension($file->url);
                if(
                    in_array(
                        $extension,
                        $config->get('extension')
                    )
                ){
                    $read = File::read($file->url);
                    $explode = explode("\n", $read);
                    $is_write = false;
                    foreach($explode as $nr => $line){
                        if(substr($line, -1, 1) === "\r"){
                            $explode[$nr] = substr($line, 0, -1);
                            $is_write = true;
                        }
                    }
                    if($is_write){
                        $write = implode("\n", $explode);
                        File::write($file->url, $write);
                        $counter++;
                    }
                }
            }
        }
        return 'Linefeed: number of changes: ' . $counter . PHP_EOL;
    }
}