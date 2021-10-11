{$json = parameter('add', 1)}
{$route = $json|json.decode}
{route.add($route)}