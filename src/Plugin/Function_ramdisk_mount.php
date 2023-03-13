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
use R3m\Io\App;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\Dir;

/**
 * @throws Exception
 */
function function_ramdisk_mount(Parse $parse, Data $data, $size='1G', $url='', $name=''){
    $object = $parse->object();
    $id = posix_geteuid();

    $param_size = App::parameter($object, 'mount', 1);
    if($param_size){
        $size = $param_size;
    }
    if (!empty($id)){
        throw new Exception('RamDisk can only be created by root...');
    }
    if(empty($name)){
        $name = Core::uuid();
//        $name = 'ram';
    }
    if(empty($url)){
        $url = $object->config('dictionary.cache') . $name . $object->config('ds');
    }
    $config_url = $object->config('project.dir.data') . 'Config' . $object->config('extension.json');
    $config = $object->data_read($config_url);
    if($config){
        $config->set('ramdisk.size', $size);
        $config->set('ramdisk.url', $url);
        $config->set('ramdisk.name', $name);
        $config->write($config_url);
    }
    Dir::create($url, 0777);
    $command = 'chown www-data:www-data ' . $url . ' -R';
    Core::execute($object, $command);
    $command = 'chmod 1777 ' . $url;
    Core::execute($object, $command);
    $mount_url = substr($url, 0, -1);
    $command = 'mount -t tmpfs -o size=' . $size . ' ' . $name .' ' . $mount_url;
    echo $command . PHP_EOL;
    Core::execute($object, $command, $output, $notification);
    if($output){
        echo $output . PHP_EOL;
    }
    if($notification){
        echo $notification . PHP_EOL;
    }
    $command = 'mount | tail -n 1';
    Core::execute($object, $command, $output);
    if($output){
        echo $output . PHP_EOL;
    }

}
