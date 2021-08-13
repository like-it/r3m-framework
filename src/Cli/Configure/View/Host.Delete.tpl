{$host = parameter('delete', 1)}
{if(is.empty($host))}
{$host = terminal.readline('Hostname: ')}
{/if}
{host.delete($host)}