{{R3M}}
{{$meta.author = 'R3m.io'}}
{{import('Main.css')}}
{{for.each(data() as $attribute => $value)}}
<h3>{{$attribute}}</h3>
{{d($value)}}
{{/for.each}}
Under construction...