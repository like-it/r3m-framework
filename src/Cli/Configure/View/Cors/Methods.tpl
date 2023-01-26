{{R3M}}
{{$deeper.test = 'deeper'}}
{{$method = []}}
{{$method['test.deep.deeper'] = terminal.readline('Method: ')}}
{{$method['test']['deep'][$deeper.test|uppercase.first]['test'] = terminal.readline('Method: ')}}
{{dd('{{$this}}')}}