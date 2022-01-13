{$node.name = parameter('url', 1)}
{if(is.empty($node.name))}
{$node.name = terminal.readline('Name: ')}
{/if}
{$node.environment = parameter('url', 2)}
{if(is.empty($node.environment))}
{$node.environment = terminal.readline('Environment (development, staging, production): ')}
{/if}
{$node.url = parameter('url', 3)}
{if(is.empty($node.url))}
{$node.url = terminal.readline('Url: ')}
{/if}
{server.url.add($node)}