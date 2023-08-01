{{R3M}}
{{if(config('silence') === true)}}
R3m.io silence mode... {{terminal.color('green')}}{{config('framework.version')}}{{terminal.color('reset')}}

{{else}}
Welcome to R3m.io {{terminal.color('green')}}{{config('framework.version')}}{{terminal.color('reset')}}


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