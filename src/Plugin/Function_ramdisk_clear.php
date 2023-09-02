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
    $object->config('ramdisk.is.disabled', true);
    $id = posix_geteuid();
    if (!empty($id)){
        throw new Exception('RamDisk clear can only be run by root...');
    }
    $config_url = $object->config('app.config.url');
    $config = $object->data_read($config_url);
    if($config){
        $size = $config->get('ramdisk.size');
        $url = $config->get('ramdisk.url');
        $name = $config->get('ramdisk.name');
        $command = 'umount ' . $url;
        Core::execute($object, $command);
        Dir::remove($url);
        $name = Core::uuid();
        $url = $object->config('framework.dir.temp') . $name . $object->config('ds');
        Dir::create($url, Dir::CHMOD);
        $command = 'mount -t tmpfs -o size=' . $size . ' ' . $name .' ' . $url;
        Core::execute($object, $command);
        $command = 'chown www-data:www-data ' . $object->config('framework.dir.temp');
        Core::execute($object, $command);
        $command = 'chown www-data:www-data ' . $url;
        Core::execute($object, $command);
        $config->set('ramdisk.size', $size);
        $config->set('ramdisk.url', $url);
        $config->set('ramdisk.name', $name);
        $config->write($config_url);
        $dir = new Dir();
        $read = $dir->read($object->config('framework.dir.temp'));
        if(is_array($read)){
            foreach ($read as $file){
                if(
                    $file->type === Dir::TYPE &&
                    $file->name !== $name &&
                    Core::is_uuid($file->name)
                ){
                    Dir::remove($file->url);
                    echo 'Removed: ' . $file->url . PHP_EOL;
                }
            }
        }
    }
    $command = 'mount | tail -n 1';
    Core::execute($object, $command, $output);
    echo $output . PHP_EOL;

}
