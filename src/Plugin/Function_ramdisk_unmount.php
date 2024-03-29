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
use R3m\Io\Module\Data;
use R3m\Io\Module\Dir;
use R3m\Io\Module\Core;

/**
 * @throws Exception
 */
function function_ramdisk_unmount(Parse $parse, Data $data, $url=''){
    $object = $parse->object();
    $object->config('ramdisk.is.disabled', true);
    $id = posix_geteuid();
    if (!empty($id)){
        throw new Exception('RamDisk can only be unmounted by root...');
    }
    $config_url = $object->config('app.config.url');
    $config = $object->data_read($config_url);
    if($config){
        $url = $object->get('ramdisk.url');
        if($url){
            $command = 'umount ' . $url;
            Core::execute($object, $command);
            Dir::remove($url);
        }
        $config->delete('ramdisk');
        $config->write($config_url);
    }
    echo 'RamDisk successfully unmounted...' . PHP_EOL;
}
