{$server.name = parameter('create', 1)}
{if(is.empty($server.name))}
{$server.name = terminal.readline('Domain name: ')}
{/if}
{$server.root = parameter('create', 2)}
{site.create($server)}