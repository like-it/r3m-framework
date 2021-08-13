Welcome to R3m.io                             (c) Remco van der Velde
{binary()} bin                            | Creates binary
{binary()} cache clear                    | Clears the app cache
{binary()} configure                      | App configuration commands
{binary()} configure domain add           | Adds a domain to /project_dir/Host
{binary()} configure environment toggle   | Toggle environment between development, staging & production
{binary()} configure host add             | Adds a host to /etc/host
{binary()} configure host create          | Create and setup an apache2 site
{binary()} configure host delete          | Delete a host from /etc/host
{binary()} configure public create        | Creates the public html directory
{binary()} configure server admin         | Set the server admin
{binary()} configure site create          | Create an apache2 site file
{binary()} configure site delete          | Delete an apache2 site file
{binary()} configure site disable         | Disable an apache2 site
{binary()} configure site enable          | Enable an apache2 site
{binary()} info                           | Info shortlist
{binary()} info all                       | This info
{binary()} license                        | R3m/framework license
{binary()} password                       | Password hash generation
{binary()} uuid                           | Uuid generation
{binary()} version                        | Version information

{binary()} doctrine orm:generate-proxies  | Genereate proxies & adjust owner

vendor/bin/doctrine orm:schema-tool:update --dump-sql --force
vendor/bin/doctrine orm:generate-proxies
