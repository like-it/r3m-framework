{$public_html = parameter('create', 1)}
{if(is.empty($public_html))}
{$public_html = terminal.readline('Directory: ')}
{/if}
{public.create($public_html)}