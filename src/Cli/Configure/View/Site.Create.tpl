{{R3M}}
{{$server.name = parameter('create', 1)}}
{{while(is.empty($server.name))}}
{{$server.name = terminal.readline('Domain name: ')}}
{{/while}
{{$server.root = parameter('create', 2)}}
{{site.create($server)}}