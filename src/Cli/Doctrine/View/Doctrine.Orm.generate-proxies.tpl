{{R3M}}
{{$is.set  = dir.set(config('project.dir.root'))}}
{{core.stream('doctrine orm:generate-proxies', 'output')}}
{{$output)}}
{{core.stream('chown www-data:www-data "/tmp" -R', 'output')}}
{{$output)}}


