{{R3M}}
{{$is.set  = dir.set(config('project.dir.root'))}}
{{core.exec('vendor/bin/doctrine orm:generate-proxies', 'output')}}
{{implode("\n", $output)}}
{{core.exec('chown www-data:www-data "/tmp" -R', 'output')}}
{{implode("\n", $output)}}


