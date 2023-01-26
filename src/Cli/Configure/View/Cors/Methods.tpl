{{R3M}}
{{$method = []}}
{{$method[] = terminal.readline('Method: ')}}
{{$method = object(parameter('methods', 1), 'array')}}
{{if(is.empty($method))}}
{{$method = []}}
{{$method[] = terminal.readline('Method: ')}}
{{while(true)}}
{{$method[] = terminal.readline('Method: ')}}
{{dd($method)}}
{{if(is.empty(array.end('method')))}}
    {{array.pop('method')}}
    {{break()}}
{{/if}}
{{/while}}
{{/if}}
{{dd($method)}}
{{cors.methods($method)}}