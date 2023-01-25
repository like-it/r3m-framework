{{R3M}}
{{$method = object(parameter('methods', 1), 'array')}}
{{if(is.empty($method))}}
{{$nr = 0}}
{{while(true)}}
{{$method.$nr = terminal.readline('Method: ')}}
{{if(is.empty($method.$nr))}}
    {{break()}}
{{/if}}
{{/while}}
{{/if}}
{{cors.methods($method)}}