{$resource = parameter('delete', 1)}
{if(is.empty($resource))}
{$resource = terminal.readline('Resource: ')}
{/if}
{route.delete($resource)}