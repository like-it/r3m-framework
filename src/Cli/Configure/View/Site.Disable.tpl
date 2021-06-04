{$server.name = parameter('disable', 1)}
{if(is.empty($server.name))}
{$server.name = terminal.readline('Domain name: ')}
{/if}
{site.disable($server)}