{$server.name = parameter('enable', 1)}
{if(is.empty($server.name))}
{$server.name = terminal.readline('Domain name: ')}
{/if}
{site.enable($server)}