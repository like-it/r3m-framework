{{R3M}}
{{$module = 'Index'}}
{{$use = json.select($controller.dir.data + '/Controller/' + $module + '.json', $module + '.use')}}
<?php
{{if(!is.empty($subdomain))}}namespace Host\{{$subdomain}}\{{$domain}}\{{$extension}}\Controller;{{else}}namespace Host\{{$domain}}\{{$extension}}\Controller;{{/if}}

{{if(is.array($use))}}
{{for.each($use as $useage)}}
use {{$useage}};
{{/for.each}}
{{/if}}

class {{$module}} extends View {

    const DIR = __DIR__ . DIRECTORY_SEPARATOR;

    {{literal}}/**
     * @throws LocateException
     * @throws ObjectException
     * @throws UrlEmptyException
     * @throws UrlNotExistException
     * @throws FileWriteException
     */{{/literal}}
     public static function overview(App $object){

        $name = {{$module}}::name(__FUNCTION__);
        $url = {{$module}}::locate($object, $name);
        return {{$module}}::response($object, $url);
     }

}