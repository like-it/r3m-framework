{{R3M}}
{{if(config('silence') === true)}}
R3m.io silence mode... {{cli.color('green')}}{{config('framework.version')}}{{cli.reset()}}

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