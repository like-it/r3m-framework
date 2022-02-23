{{R3M}}
<!DOCTYPE html>
<html lang="en">
{{require(config('framework.dir.view') + 'Error/Component/Head.tpl')}}
<body>

<header>
    <h1>Error: function ({{$route.function}}) doesn{{literal}}'{{/literal}}t exists in the controller {{$controller.title}}</h1>
</header>

<main>
    Controller {{$controller.title}} is missing a function called from a route. <br>
    Available functions: <br>
    <ul>
        {{for.each($method as $_method)}}
            <li>{{$_method}}</li>
        {{/for.each}}
    </ul>
</main>
</body>
</html>