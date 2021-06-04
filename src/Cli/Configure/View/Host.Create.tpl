{$host = parameter('create', 1)}
{if(is.empty($host))}
{$host = terminal.readline('Hostname: ')}
{/if}
{$public_html = parameter('create', 2)}
{$ip = parameter('create', 3)}
{if(is.empty($ip))}
{$ip = '0.0.0.0'}
{/if}
{$server.admin = parameter('create', 4)}
{if(is_empty($server.admin))}
{$server.admin = config.read('server.admin')}
{if(is.empty($server.admin))}
{$server.admin = terminal.readline('Server admin e-mail: ')}
{$write = server.admin($server.admin)}
{/if}
{else}
{$write = server.admin($server.admin)}
{/if}
{host.create($host, $public_html, $ip, $server.admin)}