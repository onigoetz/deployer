# Configuration

The configuration is separated in a few files, each file has a specific purpose

- `sources.php` where do the releases come from ?
- `servers.php` what servers are available to deploy to ?
- `directories.php` what is the file structure ?
- `tasks.php` Sets of tasks to execute
- `environments.php` Combine all the above in deploy sets



## `sources.php`

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



## `servers.php`

This is an array of servers, the key is the name of the server and has the following options:

#### - `host`
The hostname or IP address

#### - `username`
The SSH username to deploy

#### - `password`
The SSH password to deploy (optional as it can be requested at runtime)




## `directories.php`

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




## `tasks.php`
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