{{R3M}}
{{$meta.author = 'R3m.io'}}
{{$meta.title = 'Installed: '}}
{{import('Main.css')}}
<section name="main">
    {{if(config('framework.environment') === 'development')}}
    {{$data.contentType = $contentType}}
<details>
<summary>$contentType:</summary>
<pre>
{{object($data, 'json')}}
</pre>
</details>
{{data.delete('data')}}
{{$data.controller = $controller}}
<details>
<summary>$controller:</summary>
<pre>
{{object($data, 'json')}}
</pre>
</details>
{{data.delete('data')}}
{{$data.host = $host}}
<details>
<summary>$host:</summary>
<pre>
{{object($data, 'json')}}
</pre>
</details>
{{data.delete('data')}}
{{if(is.array($link))}}
{{for.each($link as $nr => $value)}}
{{$value = $value|html.entity.encode}}
{{data.set('data.link' + '.' + $nr, $value)}}
{{/for.each}}
{{/if}}
{{$data.link = (array) $data.link}}
<details>
<summary>$link:</summary>
<pre>
{{object($data, 'json')}}
</pre>
</details>
{{data.delete('data')}}
{{$data.meta = $meta}}
<details>
<summary>$meta:</summary>
<pre>
{{object($data, 'json')}}
</pre>
</details>
{{data.delete('data')}}
{{$data.r3m = $r3m}}
<details>
<summary>$r3m:</summary>
<pre>
{{object($data, 'json')}}
</pre>
</details>
{{data.delete('data')}}
{{$data.request = request()}}
<details>
<summary>$request (through function):</summary>
<pre>
{{object($data, 'json')}}
</pre>
</details>
{{data.delete('data')}}
{{$data.route.current = route.current()}}
{{$data.route.current.url = route.get(route.current('name'))}}
<details>
<summary>$route (through function):</summary>
<pre>
{{object($data, 'json')}}
</pre>
</details>
{{data.delete('data')}}
{{if(is.array($script))}}
{{for.each($script as $nr => $value)}}
{{$value = $value|html.entity.encode}}
{{data.set('data.script' + '.' + $nr, $value)}}
{{/for.each}}
{{/if}}
{{$data.script = (array) $data.script}}
<details>
<summary>$script:</summary>
<pre>
{{object($data, 'json')}}
</pre>
</details>
{{data.delete('data')}}
{{$data.session = session()}}
<details>
<summary>$session (through function):</summary>
<pre>
{{object($data, 'json')}}
</pre>
</details>
{{data.delete('data')}}
{{else}}
<blockquote>
In development mode this will show all defined variables.
</blockquote>
{{/if}}
</section>
