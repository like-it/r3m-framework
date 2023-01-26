{{R3M}}
{{$deeper.test = 'deeper'}}
{{$method = []}}
{{$method['test.deep.deeper'] = terminal.readline('Method: ')}}
{{$server.url = server.url('docs.r3m.io')}}
{{$method['test']['deep'][$deeper.test|uppercase.first][$server.url] = terminal.readline('Method: ')}}
{{dd('{{$this}}')}}