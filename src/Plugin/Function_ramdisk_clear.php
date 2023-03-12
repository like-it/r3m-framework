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
use R3m\Io\Module\Dir;

/**
 * @throws Exception
 */
function function_ramdisk_clear(Parse $parse, Data $data){
    $object = $parse->object();
    $id = posix_geteuid();
    if (!empty($id)){
        throw new Exception('RamDisk can only be created by root...');
    }
    $config_url = $object->config('project.dir.data') . 'Config' . $object->config('extension.json');
    $config = $object->data_read();
    if($config){
        $size = $config->get('ramdisk.size');
        $url = $config->get('ramdisk.url');
        $name = $config->get('ramdisk.name');
        $command = 'umount ' . $url;
        Dir::remove($url);
        $name = Core::uuid();
        $url = $object->config('dictionary.temp') . $name . $object->config('ds');
        Dir::create($url, Dir::CHMOD);
        $command = 'mount -t tmpfs -o size=' . $size . ' ' . $name .' ' . $url;
        Core::execute($object, $command);
        $config->set('ramdisk.size', $size);
        $config->set('ramdisk.url', $url);
        $config->set('ramdisk.name', $name);
        $config->write($config_url);
    }
    $command = 'mount | tail -n 1';
    Core::execute($object, $command, $output);
    echo $output . PHP_EOL;
}