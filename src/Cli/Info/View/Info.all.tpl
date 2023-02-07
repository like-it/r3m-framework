{{R3M}}
{{$route = route.export()}}
{{$route = data.filter($route, [
'method' => 'CLI'
])}}
{{$route = info_all_add($route)}}
Welcome to R3m.io                             {{terminal.color('blue')}}(c) Remco van der Velde {{terminal.color('green')}}({{config('framework.version')}}){{terminal.color('reset')}}

{{$route = data.sort($route, [
'info' => 'ASC'
])}}
{{for.each($route as $nr => $record)}}
{{if(is.array($record.info))}}
{{$info = implode("\n", $record.info)}}
{{parse.string($info)}}

{{elseif(!is.empty($record.info))}}
{{parse.string($record.info)}}

{{/if}}
{{/for.each}}
