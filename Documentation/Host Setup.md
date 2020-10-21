# Host Setup

For creating a new project you need to give it a domain name. this can be a public internet address but also a local environment.
If you want to develop the project, you will need a '.local' extension as your domain name.
The r3m.io framework automatically adds a '.local' extension to the routing.
Example:
Domainname: example.com

Route located in '/project/Host/Example/Com/Data/Route.json':

    "example.com/index": {
        "path": "",
        "host": [
            "example.com"
        ],
        "controller" : "Host.Example.Com.Main.Controller.Main.Overview",
        "method": [
            "GET",
            "POST"
        ]
    }

Route located in '/project/Data/Route.json':

	"Host\/Example\/Com\/Data\/Route":{
        "resource": "{$project.dir.host}Example/Com/Data/Route.json"
    }


There has to be a '.local' symlink in the Example 'domain' directory.
Create "ln -s Com Local" in "/project/Host/Example/".

In '/project/vendor/r3m/framework/Data' there is a apache2.conf file which should be copied to the '/etc/apache2/sites-available/001-domain.extension.conf' and edited:
The '{$variables}' should be replaced with the following in case of example.com:

	'{$server.name}' : "example.com"
	'{$project.directory}' : "/project"

In case of development, the '{$server.name}' should be "example.local".

In '/etc/hosts' you should add the following lines:

	127.0.0.1			example.com
	127.0.0.1			example.local

Restart apache2 with: 'service apache2 restart' and navigate with a browser to 'http://example.local'



