{$email = parameter('admin', 1)}
{if(is.empty($email))}
{$email = terminal.readline('E-mail: ')}
{/if}
{server.admin($email)}