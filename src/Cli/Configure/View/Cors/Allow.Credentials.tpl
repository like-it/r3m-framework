{{R3M}}
{{$allow = parameter('credentials', 1)}}
{{if($allow == 'false')}}
{{$allow = false}}
{{elseif($allow == 'true')}}
{$allow = true}}
{{/if}}
{{if($allow === null)}}
{{$allow = terminal.readline('Hostname: ')}}
{{if($allow == 'false')}}
{{$allow = false}}
{{elseif($allow == 'true')}}
{$allow = true}}
{{/if}}
{{/if}}
{{cors.allow.credentials($allow)}}