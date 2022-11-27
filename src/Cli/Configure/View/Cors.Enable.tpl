{{R3M}}
{{$host = parameter('enable', 1)}}
{{if(is.empty($host))}}
{{$host = terminal.readline('Hostname: ')}}
{{/if}}
{{cors.enable($host)}}