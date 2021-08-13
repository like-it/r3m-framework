{$ip = parameter('add', 1)}
{$host = parameter('add', 2)}
{if(is.empty($ip))}
{$ip = terminal.readline('Ip: ')}
{/if}
{if(is.empty($host))}
{$host = terminal.readline('Hostname: ')}
{/if}
{host.add($ip, $host)}