{{R3M}}
{{block.html()}}
<!DOCTYPE html>
<html lang="en">
{{require(config('framework.dir.view') + 'Error/Component/Head.tpl')}}
<body>
<header>
    <h1>Error: function ({{$route.function}}) doesn{{literal}}'{{/literal}}t exists in the controller ({{$controller.title}})</h1>
</header>
<main>
    Controller <u>{{$controller.title}}</u> is missing a function, which is called from a route. <br>
    Available functions: <br>
    <ul>
        {{for.each($method as $_method)}}
            <li>{{$_method}}</li>
        {{/for.each}}
    </ul>
    Needed function: <strong>{{$route.function}}</strong>.
</main>
</body>
</html>
{{/block.html}}