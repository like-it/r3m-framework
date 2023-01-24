{{R3M}}
{{$host = parameter('default', 1)}}
{{if(is.empty($host))}}
{{$host = terminal.readline('Hostname: ')}}
{{/if}}
{{cors.setup.default($host)}}