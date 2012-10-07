# Deployer

Deployer is a tool that helps to deploy projects of any type to a server with minimal requirements.

Requirements :

* SSH Connection to the server
* PHP if you want to use composer

The library is a work in progress and could do a lot more things but for the moment it does what I need it to.

## Why this new library ?

There are so much tools to deploy an application to one or many servers, the problem is that they're hard to configure and / or they need to install software on the destination server.

I wanted my servers to stay as clean as possible. So I went up with this little tool.

Deploying is as simple as :

	./deploy deploy production

Or to be clearer :

	./deploy [command] [environment]

## Installation

You can install the library through [composer](http://getcomposer.org). All you need is to add it as dependency to your composer.json.

```json
{
    "require-dev": {
        "onigoetz/deployer": "dev-master"
    }
}
```

## Configuration

the configuration is done in the script file itself, 
```php
<?php

$config = array(
    ...
);

include 'vendor/autoload.php';
\Deployer\Init::bootstrap($config);
```

Detailed configuration options can be found on [this page](https://github.com/onigoetz/deployer/wiki/Options)