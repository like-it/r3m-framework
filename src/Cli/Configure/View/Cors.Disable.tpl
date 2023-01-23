{{R3M}}
{{$host = parameter('disable', 1)}}
{{if(is.empty($host))}}
{{$host = terminal.readline('Hostname: ')}}
{{/if}}
{{cors.disable.domain($host)}}