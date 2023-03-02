{{R3M}}
{{$is.set  = dir.set(config('project.dir.root'))}}
{{core.exec('doctrine orm:generate-proxies', 'output')}}
{{$output)}}
{{$output_error)}}
{{dd('{{$this}}')}}

{{core.exec('chown www-data:www-data "/tmp" -R', 'output')}}
{{$output)}}


