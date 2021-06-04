{$server.name = parameter('delete', 1)}
{if(is.empty($server.name))}
{$server.name = terminal.readline('Domain name: ')}
{/if}
{site.delete($server)}