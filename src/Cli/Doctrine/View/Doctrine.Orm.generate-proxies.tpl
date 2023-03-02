{{R3M}}
{{$is.set  = dir.set(config('project.dir.root'))}}
{{core.stream('doctrine orm:generate-proxies', 'output')}}
{{$output)}}
{{$output_error)}}

{{if(config('doctrine.proxy.dir'))}}
{{$dir.core.stream = config('doctrine.proxy.dir')}}
{{if(string.substring($dir.core.stream, -1, 1) === '/')}}
{{$dir.core.stream = string.substring($dir.core.stream, 0, -1)}}
{{/if}}
{{core.stream('chown www-data:www-data "{{$dir.core.stream}}" -R', 'output')}}
{{/if}}

{{$output)}}


