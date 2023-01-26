{{R3M}}
{{$deeper.test = 'deeper'}}
{{$method = []}}
{{$method['test.deep.deeper'] = terminal.readline('Method: ')}}
{{$method['test']['deep'][$deeper.test|uppercase.first]["{{server.url('docs.r3m.io')}}"] = terminal.readline('Method: ')}}
{{dd('{{$this}}')}}