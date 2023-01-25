{{R3M}}
{{$method = object(parameter('methods', 1), 'array')}}
{{if(is.empty($method))}}
{{$method = []}}
{{while(true)}}
{{$method[] = terminal.readline('Method: ')}}
{{if(is.empty(array.end($method)))}}
    {{array.pop($method)}}
    {{break()}}
{{/if}}
{{/while}}
{{/if}}
{{cors.methods($method)}}