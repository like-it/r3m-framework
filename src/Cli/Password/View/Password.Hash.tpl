{$password = terminal.readline('Password: ')}
{$cost = terminal.readline('Cost (13):')}
{if(is.empty($cost))}
{$cost = 13}
{/if}{password.hash($password, $cost)}

