# Content Hub Client CLI
A command line interface for the Acquia Content Hub Client built in PHP.

## Installation
Clone the repository and run composer to install.

```
git clone git@github.com:fiasco/content-hub-client-cli.git
cd content-hub-client-cli
composer install
./src/bin/content-hub
```

## Local clients
You can store existing client information locally at ~/.content-hub/clients/ and pass them as parameter to the CLI:
```
./src/bin/content-hub --client=clientname client:list
```
See clientname.example for syntax. If you don't already have a client registered, you can register one with this tool
also, using client:register.

## Register clients in bulk
```
./src/bin/content-hub --client=toyota.prod client:register site1.prod site2.prod site3.prod
```