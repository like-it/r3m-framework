{{R3M}}
{{$is.set  = dir.set(config('project.dir.root'))}}
{{core.execute('doctrine orm:generate-proxies', 'output')}}
{{$output_notification)}}

{{if(config('doctrine.proxy.dir'))}}
{{$dir.core.stream = config('doctrine.proxy.dir')}}
{{if(string.substring($dir.core.stream, -1, 1) === '/')}}
{{$dir.core.stream = string.substring($dir.core.stream, 0, -1)}}
{{/if}}
{{core.execute('chown www-data:www-data "' + $dir.core.stream + '" -R', 'output')}}
{{/if}}
{{$output_notification)}}
