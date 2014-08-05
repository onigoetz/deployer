# Deployer
### A Laravel 4 and standalone deployment system



Deployer is a tool that helps to deploy projects of any type to a server with minimal requirements.

Requirements :

* SSH Connection to the server
* PHP if you want to use composer

The library is a work in progress and could do a lot more things but for the moment it does what I need it to.

## Why this new library ?

There are so much tools to deploy an application to one or many servers, the problem is that they're hard to configure and / or they need to install software on the destination server.

I wanted my servers to stay as clean as possible. So I went up with this little tool.

Deploying is as simple as :

	./deploy server:deploy production

Or to be clearer :

	./deploy [command] [environment]

## Commands

Two commands are available

- server:deploy : To deploy the latest commit on one or more servers
- server:rollback : To rollback the latest deploy on one or more servers

## Installation

You can install the library through [composer](http://getcomposer.org). All you need is to add it as dependency to your composer.json.

```json
{
    "require-dev": {
        "onigoetz/deployer": "1.0.0-beta1"
    }
}
```

## Configuration

There are two ways to configure the deployer, in both cases, the idea is that an array holds the complete configuration,
it's easier to maintain an use than a lot of different configuration classes.

The syntax is explained [here](https://github.com/onigoetz/deployer/wiki/Options)

### In Laravel 4

Add the following lines to your application in `config/local/app.php`

```php

    'providers' => append_config(
        ['Onigoetz\Deployer\DeployServiceProvider']
    ),

```

Then create a config file named `deploy.php` in `app/config`
```php
<?php

return array(
    ...
);
```

Then you can run `./artisan server:deploy production` to deploy

### Standalone launcher

This time the configuration is done in the executable script.

Create a file named `deploy` (actually you can name it the way you want)
```php
#!/usr/bin/env php
<?php

$config = array(
    ...
);

include 'vendor/autoload.php';
\Deployer\Init::run($config);
```

Chmod the file to executable

Then you can run `./deploy server:deploy production` to deploy
