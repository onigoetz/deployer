---
layout: default
---

## Introduction

Deployer is a tool that helps to deploy projects of any type to a server with minimal requirements.

Requirements :

* SSH Connection to the server
* PHP if you want to use composer

The library is a work in progress and could do a lot more things but for the moment it does what I need it to.

## Usage

Deploying is as simple as :

	vendor/bin/deployer server:deploy production

Two commands are available

- `server:deploy` : To deploy the latest commit on one or more servers
- `server:rollback` : To rollback the latest deploy on one or more servers

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

With that part configured you can run `./artisan config:publish onigoetz/deployer`

this will copy the default configurations to `config/packages/onigoetz/deployer`
You can find the details of each file in the [Configuration section](#configuration-options)

Then you can run `./artisan server:deploy production` to deploy

### Standalone launcher

This time the configuration is made in a folder called `.deployer`

to do this, add deployer to your composer dependencies and do

```bash
cp vendor/onigoetz/deployer/src/config .deployer
```
You can now configure your infrastructure.

Then you can run `vendor/bin/deployer server:deploy production` to deploy

## Configuration Options

The configuration is separated in a few files, each file has a specific purpose

- `sources.php` where do the releases come from ?
- `servers.php` what servers are available to deploy to ?
- `directories.php` what is the file structure ?
- `tasks.php` Sets of tasks to execute
- `environments.php` Combine all the above in deploy sets

### `sources.php`

This is an array of sources, the key is the name of the source and has the following options:

#### Global Options

__- `strategy`__<br />
- `clone` will clone a git repository
- `upload` will duplicate the current repository, zip and upload it.

__- `path`__<br />
Where to take the release from

For the `upload` strategy, specify an absolute path on your machine

For the `clone` strategy you have two options.

1. Fill the full path to the repository (like `git@github.com:onigoetz/deployer.git`)
2. Provide an array with `repository`, `username` and `password` (the password is optional as it can be requested at runtime)

#### Clone Specific options

__- `type`__<br />
Only `git` is available at the moment

__- `branch`__<br />
`master` by default

__- `submodules`__<br />
`true` or `false` if you want to include submodules or not

#### Upload specific options

__- `include`__<br />
an array of paths to include in the build

__- `exclude`__<br />
an array of paths to exclude from the build

#### Example

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


### `servers.php`

This is an array of servers, the key is the name of the server and has the following options:

__- `host`__<br />
The hostname or IP address

__- `username`__<br />
The SSH username to deploy

__- `password`__<br />
The SSH password to deploy (optional as it can be requested at runtime)


### `directories.php`

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

__- `root`__<br />
The main folder

__- `binaries`__<br />
The binaries folder, contains all snapshots, current and old ones (`binaries` by default)

__- `binary_name`__<br />
The folder name that will contain the binaries (`%G-%m-%d_%H-%M` by default)
Uses strftime : [http://php.net/strftime]()

__- `deploy`__<br />
Symlink to the current version (`www` by default)




### `tasks.php`
//TO DEFINE


### `environments.php`
This is an array of environments, the key is the name of the environment and has the following options:

__- `source`__<br />
The source name, defined in `sources.php`

__- `servers`__<br />
an array of servers, defined in  `servers.php`

__- `overrides`__<br />
an array of overrides, with this you can override :
- source
- directories

with the same syntax and options as in the corresponding files
