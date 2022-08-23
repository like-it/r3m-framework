{{R3M}}
{{if(config('silence') === true)}}
R3m.io silence mode... {{cli.background.color('set')}}{{config('framework.version')}}{{cli.reset()}}
{{cli.background.color('black')}}{{config('framework.version')}}{{cli.reset()}}
{{cli.background.color('red')}}{{config('framework.version')}}{{cli.reset()}}
{{cli.background.color('blue')}}{{config('framework.version')}}{{cli.reset()}}
{{cli.background.color('yellow')}}{{config('framework.version')}}{{cli.reset()}}
{{cli.background.color('green')}}{{config('framework.version')}}{{cli.reset()}}
{{cli.background.color('purple')}}{{config('framework.version')}}{{cli.reset()}}
{{cli.background.color('lightgrey')}}{{config('framework.version')}}{{cli.reset()}}
{{cli.background.color('set')}}{{config('framework.version')}}{{cli.reset()}}


{{else}}
Welcome to R3m.io {{cli.color('green')}}{{config('framework.version')}}{{cli.reset()}}


{{binary()}} bin                            | Creates binary
{{binary()}} cache clear                    | Clears the app cache
{{binary()}} configure                      | App configuration commands
{{binary()}} info                           | This info
{{binary()}} info all                       | All info
{{binary()}} license                        | R3m/framework license
{{binary()}} password                       | Password hash generation
{{binary()}} uuid                           | Uuid generation
{{binary()}} version                        | Version information
{{/if}}