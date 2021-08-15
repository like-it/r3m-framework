{$route = route.export()}
{$route = filter($route, [
'method' => 'CLI'
])}
{$route = info_all_add($route)}
Welcome to R3m.io                             (c) Remco van der Velde ({config('framework.version')})
{$route = sort($route, [
'info' => 'ASC'
])}
{for.each($route as $nr => $record)}
{if(is.array($record.info))}
{$info = implode("\n", $record.info)}
{parse.string($info)}

{elseif(!is.empty($record.info))}
{parse.string($record.info)}

{/if}
{/for.each}

vendor/bin/doctrine orm:schema-tool:update --dump-sql --force