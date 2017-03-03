# RabbitMqManagerBundle
[![Latest Version](https://img.shields.io/github/release/MyOnlineStore/rabbitmq-manager-bundle.svg?style=flat-square)](https://github.com/MyOnlineStore/rabbitmq-manager-bundle/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/travis/MyOnlineStore/rabbitmq-manager-bundle.svg?style=flat-square)](https://travis-ci.org/MyOnlineStore/rabbitmq-manager-bundle)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/MyOnlineStore/rabbitmq-manager-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/MyOnlineStore/rabbitmq-manager-bundle)
[![Quality Score](https://img.shields.io/scrutinizer/g/MyOnlineStore/rabbitmq-manager-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/MyOnlineStore/rabbitmq-manager-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/MyOnlineStore/rabbitmq-manager-bundle.svg?style=flat-square)](https://packagist.org/packages/MyOnlineStore/rabbitmq-manager-bundle)

## Installation

Require the bundle and its dependencies with composer:
```shell
$ composer require myonlinestore/rabbitmq-manager-bundle
```

Register the bundle into your `app/AppKernel.php`:
```php
public function registerBundles()
{
    $bundles = [
        new MyOnlineStore\Bundle\RabbitMqManagerBundle\RabbitMqManagerBundle(),
    ];
}
```

## Usage

All configuration options are optional.

### Full Default Configuration

```yaml
rabbit_mq_manager:
    path: "%kernel.root_dir%/../var/supervisor/%kernel.name%"
    commands: # console commands to execute for specific worker types
      cli_consumer_invoker: "rabbitmq-manager:consumer" # this will use the rabbitmq-cli-consumer invoker, defined within this package.
      consumers: "rabbitmq:consumer" # OldSoundRabbitMqBundle default consumer command
      rpc_servers: "rabbitmq:rpc-server" # OldSoundRabbitMqBundle default rpc-server command
    consumers:
      general:
        processor: "bundle" # either "bundle" or "cli-consumer"
        messages: 0 # amount of messages to process before restarting the consumer (only applicable for processor "bundle")
        compression: true # use compression (only applicable for processor "cli-consumer")
        worker: # http://supervisord.org/configuration.html#program-x-section-values
          count: 1
          startsecs: 0
          autorestart: true
          stopsignal: "INT"
          stopasgroup: true
          stopwaitsecs: 60
      individual:
        # my_consumer:
          # see general configuration
    rpc_servers: # see consumers
```

### Example configuration
```yaml
rabbit_mq_manager:
    consumers:
      general:
        processor: "bundle"
        messages: 1 # only consume 1 message before quiting/restarting
        worker: # http://supervisord.org/configuration.html#program-x-section-values
          count: 1
      individual:
        my_consumer:
          messages: 0 # don't restart "my_consumer" after consuming messages
          worker:
              count: 8 # start 8 listeners/processors for "my_consumer"
        my_other_consumer:
          processor: "cli-consumer"
          compression: true
          worker:
            count: 4
```
