{{R3M}}
{{$method = parameter('methods', 1)}}
{{if(is.empty($method))}}
{{$method = terminal.readline('Methods: ')}}
{{/if}}
{{cors.methods($method)}}