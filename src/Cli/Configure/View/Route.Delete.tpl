{{R3M}}
{{$resource = parameter('delete', 1)}}
{{while(is.empty($resource))}}
{{$resource = terminal.readline('Resource: ')}}
{{/while}}
{{route.delete($resource)}}