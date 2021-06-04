{$domain = parameter('add', 1)}
{if(is.empty($domain))}
{$domain = terminal.readline('Domain: ')}
{/if}
{domain.add($domain)}