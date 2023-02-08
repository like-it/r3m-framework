{{R3M}}
{{$host = parameter('create', 1)}}
{{while(is.empty($host))}}
{{$host = terminal.readline('Hostname: ')}}
{{/while}}
{{$public_html = parameter('create', 2)}}
{{$ip = parameter('create', 3)}}
{{if(is.empty($ip))}}
{{$ip = '0.0.0.0'}}
{{/if}}
{{$server.admin = parameter('create', 4)}}
{{if(is_empty($server.admin))}}
{{$server.admin = config.read('server.admin')}}
{{while(is.empty($server.admin))}}
{{$server.admin = terminal.readline('Server admin e-mail: ')}}
{{if(!is.empty($server.admin))}}
{{$write = server.admin($server.admin)}}
{{/if}}
{{/while}}
{{else}}
{{$write = server.admin($server.admin)}}
{{/if}}
{{host.setup($host, $public_html, $ip, $server.admin)}}