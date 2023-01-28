{{R3M}}
{{$age = parameter('max-age', 1)}}
{{if($age === null)}}
{{$age = terminal.readline('Max-age: ')}}
{{else.if($age === 'false')}}
{{$age = false}}
{{else}}
{{$age = (int) $age}}
{{/if}}
{{cors.max.age($age)}}