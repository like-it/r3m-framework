<?php
/**
 * @author          Remco van der Velde
 * @since           2021-03-05
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_import_translation(Parse $parse, Data $data){
    $object = $parse->object();
    $url = $object->config('framework.dir.data') . 'Translation.json';
    $framework_translation = $object->parse_read($url, sha1($url));
    if($framework_translation){
        foreach($framework_translation->data() as $uuid => $record){
            if(property_exists($record, 'resource')){
                $translation = $object->parse_read($record->resource, sha1($record->resource));
                if($translation){
                    $original = $object->data('translation');
                    if(empty($original)){
                        $original = $translation->data();
                        $object->data('translation', $original);
                    } else {
                        $object->data('translation', Core::object_merge($original, $translation->data()));
                    }
                }

            }
        }
    }
    $url = $object->config('host.dir.data') . 'Translation.json';
    $read = $object->parse_read($url, sha1($url));
    if($read === false){
        $url = $object->config('project.dir.data') . 'Translation.json';
        $read = $object->parse_read($url, sha1($url));
    }
    if($read){
        foreach($read->data() as $uuid => $record){
            if(property_exists($record, 'resource')){
                $translation = $object->parse_read($record->resource, sha1($record->resource));
                if($translation){
                    $original = $object->data('translation');
                    if(empty($original)){
                        $original = $translation->data();
                        $object->data('translation', $original);
                    } else {
                        $object->data('translation', Core::object_merge($original, $translation->data()));
                    }
                }
            }
        }
    }
}
