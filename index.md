---
layout: default
---

# Introduction

Deployer is a tool that helps to deploy projects of any type to a server with minimal requirements.

Requirements :

* SSH Connection to the server
* PHP if you want to use composer

The library is a work in progress and could do a lot more things but for the moment it does what I need it to.

## Usage

Deploying is as simple as :

	vendor/bin/deployer server:deploy production

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

# Configuration

The configuration is separated in a few files, each file has a specific purpose

- `sources.php` where do the releases come from ?
- `servers.php` what servers are available to deploy to ?
- `directories.php` what is the file structure ?
- `tasks.php` Sets of tasks to execute
- `environments.php` Combine all the above in deploy sets



## `sources.php`

This is an array of sources, the key is the name of the source and has the following options:

### Global Options

#### - `strategy`
- `clone` will clone a git repository
- `upload` will duplicate the current repository, zip and upload it.

#### - `path`
Where to take the release from

For the `upload` strategy, specify an absolute path on your machine

For the `clone` strategy you have two options.

1. Fill the full path to the repository (like `git@github.com:onigoetz/deployer.git`)
2. Provide an array with `repository`, `username` and `password` (the password is optional as it can be requested at runtime)

### Clone Specific options

#### - `type`
Only `git` is available at the moment

#### - `branch`
`master` by default

#### - `submodules`
`true` or `false` if you want to include submodules or not

### Upload specific options

#### - `include`
an array of paths to include in the build

#### - `exclude`
an array of paths to exclude from the build

### Example

```
<?php

return array(
	'source_name' => array(
	    'strategy' => 'clone',
	
		'path' => 'git@github.com:onigoetz/deployer.git',
		'type' => 'git',
	),
)

```



## `servers.php`

This is an array of servers, the key is the name of the server and has the following options:

#### - `host`
The hostname or IP address

#### - `username`
The SSH username to deploy

#### - `password`
The SSH password to deploy (optional as it can be requested at runtime)




## `directories.php`

This file contains the directories where the files will be put.

the structure is as follows :

```
{$root}/
    {$binaries}/
        {$binary_name}/
        {$binary_name}/
        ...
    {$deploy}/
                      
```

#### - `root`
The main folder, will contain the others

#### - `binaries`
The binaries folder, contains all snapshots, current and old ones (`binaries` by default)

#### - `binary_name`
The folder name that will contain the binaries (`%G-%m-%d_%H-%M` by default)
Uses strftime : [http://php.net/strftime]()

#### - `deploy`
Symlink to the current version (`www` by default)




## `tasks.php`
//TO DEFINE


## `environments.php`
This is an array of environments, the key is the name of the environment and has the following options:

#### - `source`
The source name, defined in `sources.php`

#### - `servers`
an array of servers, defined in  `servers.php`

#### - `overrides`
an array of overrides, with this you can override :
- source
- directories

with the same syntax and options as in the corresponding files