{{R3M}}
{{$url = config('framework.dir.root') + 'LICENSE'}}
{{if (file.exist($url))}}
{{file.read($url)}}
{{else}}
{{$url}}
License file doesn{{literal}}'{{/literal}}t exist.
{{/if}}