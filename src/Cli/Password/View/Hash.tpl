{$password = terminal.readline('Password: ')}{$cost = terminal.readline('Cost (12):')}{if(is.empty($cost))}{$cost = 12}{/if}{password.hash($password, $cost)}
