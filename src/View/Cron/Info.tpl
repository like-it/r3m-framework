{terminal.color('white')}Information about: {terminal.color('light-green-blue')}<{binary()}> Cron{terminal.color('reset')}
Powered by {terminal.color('light-green')}\R3m\Io{terminal.color('reset')} Version: {$r3m.config.framework.version} (c) 2012 - {date('Y')} By {terminal.color('white')}Remco van der Velde{terminal.color('reset')}

{terminal.color('light-green-blue')}<required>  {terminal.color('reset')} {terminal.color('white')} These atrributes are required. {terminal.color('reset')}
{terminal.color('light-green')}<optional>  {terminal.color('reset')} {terminal.color('white')} These atrributes are optional. {terminal.color('reset')}
{$rows = terminal.put('rows')}{$columns = terminal.put('columns')}
{str.repeat('_', $columns)}
Commands:

{terminal.color('white')}{binary()} service cron info{terminal.color('reset')}
{terminal.color('white')}{binary()} service cron start{terminal.color('reset')}
{terminal.color('white')}{binary()} service cron stop{terminal.color('reset')}
