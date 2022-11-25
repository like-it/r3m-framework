{{R3M}}
{{$meta.author = 'R3m.io'}}
{{$meta.title = 'Installed: '}}
{{import('Main.css')}}
<section name="main">
<pre>
{{$data.contentType = $contentType}}
{{$data.controller = $controller}}
{{$data.host = $host}}
{{$data.r3m = $r3m}}
{{$data.route.current = route.current()}}
{{$data.route.current.url = route.get(route.current('name'))}}
{{object($data, 'json')}}

</pre>
</section>