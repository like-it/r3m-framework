{{R3M}}
{{$age = parameter('max-age', 1)}}
{{if($age === null)}}
{{$age = terminal.readline('Max-age: ')}}
{{/if}}
{{if($age === 'false')}}
{{$age = false}}
{{/if}}
{{cors.max.age($age)}}