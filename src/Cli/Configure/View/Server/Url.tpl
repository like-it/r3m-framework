{{R3M}}
{{$node.name = parameter('url', 1)}}
{{while(is.empty($node.name))}}
{{$node.name = terminal.readline('Name: ')}}
{{/while}}
{{$node.environment = parameter('url', 2)}}
{{while(is.empty($node.environment))}}
{{$node.environment = terminal.readline('Environment (development, staging, production): ')}}
{{/while}}
{{$node.url = parameter('url', 3)}}
{{while(is.empty($node.url))}}
{{$node.url = terminal.readline('Url: ')}}
{{/while}}
{{server.url.add($node)}}