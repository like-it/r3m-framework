{{R3M}}
{{$resource = parameter('resource', 1)}}
{{while(is.empty($resource)}}
{{$resource = terminal.readline('Resource: ')}}
{{/while}}
{{route.resource($resource)}}