<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-13
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;

/**
 * @throws Exception
 */
function function_ramdisk_speedtest(Parse $parse, Data $data){
    $object = $parse->object();
    $id = posix_geteuid();
    if (!empty($id)){
        throw new Exception('RamDisk speedtest can only be run by root...');
    }
    $config_url = $object->config('project.dir.data') . 'Config' . $object->config('extension.json');
    $config = $object->data_read($config_url);
    if($config){
        $url = $config->get('ramdisk.url');
        if($url){
            $command = 'dd if=/dev/zero of=' . $url . 'zero bs=4k count=100000';
            Core::execute($object, $command, $output);
            echo 'Write:' . PHP_EOL;
            echo $output . PHP_EOL;
            $command = 'dd if=' . $url . 'zero of=/dev/null bs=4k count=100000';
            Core::execute($object, $command, $output);
            Echo 'Read:' . PHP_EOL;
            echo $output . PHP_EOL;
            File::delete($url . 'zero');
        }
    }
}
