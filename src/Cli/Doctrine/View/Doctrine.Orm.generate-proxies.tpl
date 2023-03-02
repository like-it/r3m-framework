{{R3M}}
{{$is.set  = dir.set(config('project.dir.root'))}}
{{$before = dir.read('/tmp/', false)}}
{{$before = dir.add.mtime($before)}}
{{core.stream('doctrine orm:generate-proxies', 'output')}}
{{$after = dir.read('/tmp/', false)}}
{{$after = dir.add.mtime($before)}}
{{$difference = []}}
{{for.each($after as $record)}}
    {{$is.found = false}}
    {{for.each($before as $file)}}
        {{if($file.url === $record.url)}}
            {{$is_found = $record}}
            {{break()}}
        {{/if}}
    {{/for.each}}
    {{if($is_found)}}
        {{if($record.mtime !== $is_found.mtime)}}
            {{$difference[] = $record}}
        {{/if}}
    {{else}}
        {{$difference[] = $record}}
    {{/if}}
{{/for.each}}

{{$output)}}
{{$output_error)}}

{{dd('{{$this}}')}}

{{core.stream('chown www-data:www-data "/tmp" -R', 'output')}}
{{$output)}}


