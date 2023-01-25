{{R3M}}
{{$deeper.test = 'deeper'}}
{{$method = []}}
{{$method['test.deep.deeper'] = terminal.readline('Method: ')}}
{{$method['test']['deep'][$deeper.test|uppercase.first] = terminal.readline('Method: ')}}
{{dd($method)}}