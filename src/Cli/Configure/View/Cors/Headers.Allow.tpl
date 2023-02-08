{{R3M}}
{{$headers = object(parameter('allow', 1), 'array')}}
{{if(is.empty($headers))}}
{{$headers = []}}
{{$headers[] = terminal.readline('Header allow: ')}}
{{while(true)}}
{{$headers[] = terminal.readline('Header allow: ')}}
{{if(is.empty(array.end('headers')))}}
{{array.pop('headers')}}
{{break()}}
{{/if}}
{{/while}}
{{/if}}
{{cors.headers.allow($headers)}}