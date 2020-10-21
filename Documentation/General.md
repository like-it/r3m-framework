## Welcome

The R3m.io framework has the following elements:

- Routing
- Model View Control (MVC)
- Template engine
- Template plugins
- CLI

To setup an instance of R3m.io go to the Installation documentation.
When setting up a host environment you will find that you get a certain folder structure.

In the root of your project folder you should have the following folders:

- Bin
- Data
- Host
- Public
- vendor

### Bin
this directory contains a r3m.php file which is used to use r3m.io in CLI

### Data
The main config and main route file can be found here. Also Cache & Compile files can be found in here.

### Host
In this directory you specify the host specific needs of your application. Also custom CLI options should be inside the Host because no other directory . There should always be a Local symlink or similar to get into development of the project.

if you have a subdomain the directory structure will look like:

- Host/subdomain/domain/extension/

if dont have a subdomain the directory structure will look like:

- Host/domain/extension/

For more information about host setup, look at Host Setup.md

### Public

the first public entrypoint of your application. this is where apache2 connects to, to serve pages and files. Host specific files goes somewhere in the Host directory explained Host Setup.md.






