<?php
/**
 * @author          Remco van der Velde
 * @since           2021-03-05
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_import_translation(Parse $parse, Data $data){
    $object = $parse->object();
    $url = $object->config('controller.dir.data') . $object->config('dictionary.translation') . $object->config('ds');
    $dir = new Dir();
    $read = $dir->read($url);
    if($read){
        foreach($read as $nr => $file){
            $file->basename = File::basename($file->name, $object->config('extension.json'));
            $translation = $object->data_read($file->url, sha1($file->url), true);
            if($translation){
                $object->data('translation.' . strtolower($file->basename), $translation->data());
            }
        }
    }
}
