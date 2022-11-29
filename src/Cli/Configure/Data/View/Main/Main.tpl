{{R3M}}
{{$request.request = request('request')}}
{{if($request.request === '/')}}
{{$request.request = ''}}
{{/if}}
{{$read = parse.read(config('host.dir.data') + 'Main.json')}}<!DOCTYPE html>
<html lang="en">
        <head>
            <meta name="author" content="{{$html.head.author|default:''}}">
            <meta http-equiv="content-type" content="{{$html.head.content.type | default:'text/html; charset=UTF-8'}}">
            <meta http-equiv="X-UA-Compatible" content="{{$html.head.compatible|default:'IE=edge,chrome=1'}}">
            <meta name="viewport" content="{{$html.head.viewport|default:'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0'}}">
            <title>{{$html.head.title|default:''}}</title>
            <meta name="revisit-after" content="{{$html.head.revisit|default:'7 days'}}">
            <meta name="rating" content="{{$html.head.rating|default:'general'}}">
            <meta name="distribution" content="{{$html.head.distribution|default:'global'}}">
            <meta name="keywords" content="{{$html.head.keywords}}">
            <meta name="description" content="{{$html.head.description|default:''}}">

			<link rel="shortcut icon" href="{{$html.head.icon|default:''}}">
            <link rel="alternate" hreflang="x-default" href="{{config('host.url')}}">
            <link rel="canonical" href="{{config('host.url')}}{{$request.request}}">
            {{if (!is.empty($script))}}
{{implode("\n\t\t\t", $script)}}
            {{/if}}
            {{if (!is.empty($link))}}
{{implode("\n\t\t\t", $link)}}
            {{/if}}
    </head>
    <body>
{{$html.content.body|default:""}}
    </body>
</html>