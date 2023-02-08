{{R3M}}
{{$headers = object(parameter('expose', 1), 'array')}}
{{if(is.empty($headers))}}
{{$headers = []}}
{{$headers[] = terminal.readline('Header expose: ')}}
{{while(true)}}
{{$headers[] = terminal.readline('Header expose: ')}}
{{if(is.empty(array.end('headers')))}}
{{array.pop('headers')}}
{{break()}}
{{/if}}
{{/while}}
{{/if}}
{{cors.headers.expose($headers)}}