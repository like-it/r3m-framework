{{R3M}}
{{while(true)}}
{{$password = terminal.readline('Password: ', 'hidden')}}
{{$again = terminal.readline('Password again: ', 'hidden')}}
{{if($password === $again)}}
    {{break()}}
{{else}}
Password mismatch.
{{/if}}
{{/while}}
{{$cost = terminal.readline('Cost (13):')}}
{{if(is.empty($cost))}}
{{$cost = 13}}
{{/if}}{{password.hash($password, $cost)}}

