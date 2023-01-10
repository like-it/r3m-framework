{{R3M}}
{{if(
$controller.dir.data &&
$module
)}}
{{$use = json.select($controller.dir.data + '/Controller/' + $module + '.json', $module + '.use')}}
{{/if}}
<?php
{{if(!is.empty($subdomain))}}namespace Host\{{$subdomain}}\{{$domain}}\{{$extension}}\Controller;{{else}}namespace Host\{{$domain}}\{{$extension}}\Controller;{{/if}}

{{if(is.array($use))}}
{{for.each($use as $useage)}}
use {{$useage}};
{{/for.each}}
{{/if}}

class {{$module}} extends Controller {

    const DIR = __DIR__ . DIRECTORY_SEPARATOR;

    {{literal}}/**
     * @throws LocateException
     * @throws ObjectException
     * @throws UrlEmptyException
     * @throws UrlNotExistException
     * @throws FileWriteException
     * @throws Exception
     */{{/literal}}
     public static function overview(App $object){

        $name = {{$module}}::name(__FUNCTION__, __CLASS__, '/');
        if(App::contentType($object) == App::CONTENT_TYPE_HTML){

            $url = {{$module}}::locate($object, 'Main/Main');
            $object->data('template.name', $name);
            $object->data('template.dir', {{$module}}::DIR);
            $view = {{$module}}::response($object, $url);
        } else {

            $url = {{$module}}::locate($object, $name);
            $view = {{$module}}::response($object, $url);
        }

        return $view;
     }
}