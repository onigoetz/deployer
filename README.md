# Deployer

[![Build Status](http://img.shields.io/travis/onigoetz/deployer.svg?style=flat)](https://travis-ci.org/onigoetz/deployer)
[![Latest Stable Version](http://img.shields.io/packagist/v/onigoetz/deployer.svg?style=flat)](https://packagist.org/packages/onigoetz/deployer)
[![Total Downloads](http://img.shields.io/packagist/dt/onigoetz/deployer.svg?style=flat)](https://packagist.org/packages/onigoetz/deployer)
[![Scrutinizer Quality Score](http://img.shields.io/scrutinizer/g/onigoetz/deployer.svg?style=flat)](https://scrutinizer-ci.com/g/onigoetz/deployer/)
[![Code Coverage](http://img.shields.io/scrutinizer/coverage/g/onigoetz/deployer.svg?style=flat)](https://scrutinizer-ci.com/g/onigoetz/deployer/)

### Deprecated

This package is deprecated, and won't receive any update.
There are most certainly better tools to do this.

### Easily deploy your applications


Deployer is a tool that helps to deploy projects of any type to a server with minimal requirements.

Requirements on the server side:

* SSH Access to the server
* PHP if you want to use composer

The library works perfectly and is used in production for a few years now. It is still marked as beta as there are some things I still want to improve.

## What does it do better than others ?

There are so much tools to deploy an application to one or many servers, the problem is that they're hard to configure and / or they need to install software on the destination server.

This tool has two advantages:
- Nothing needs to be installed on the destination
- Very simple configuration

Deploying is as simple as :

	./deploy server:deploy production

## Commands

Two commands are available

- server:deploy : To deploy the latest commit on one or more servers
- server:rollback : To rollback the latest deploy on one or more servers

## Installation

You can install the library through [composer](http://getcomposer.org). All you need is to add it as dependency to your composer.json.

```json
{
    "require-dev": {
        "onigoetz/deployer": "1.0.0-beta2"
    }
}
```

## Quick Start

> The full documentation is available at
>
> [http://onigoetz.ch/deployer/]()



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

## Roadmap

The ideas I have partly implemented or that I want to implement

- Connect to servers with SSH Keys instead of the usual username:password combo
- Create a zip locally and upload it to the server. (instead of a zip clone)
