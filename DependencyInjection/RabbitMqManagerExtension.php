<?php

namespace MyOnlineStore\RabbitMqManagerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class RabbitMqManagerExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @inheritdoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        // check that commands do not contain sprintf specifiers that were required by older versions
        foreach ($config['commands'] as $command) {
            if (false !== strpos($command, '%')) {
                throw new InvalidConfigurationException(sprintf(
                    'Invalid configuration for path "%s": %s',
                    'rabbit_mq_supervisor.commands',
                    'command is no longer allowed to contain sprintf specifiers (e.g. "%1$d")'
                ));
            }
        }

        $configuration = [
            'path' => $config['path'],
            'consumers' => [],
            'rpc_servers' => [],
        ];

        $rabbitMqBundleConfiguration = $container->getParameter('mos_old_sound_rabbit_mq.config');

        foreach (['consumers', 'rpc_servers'] as $type) {
            if (isset($rabbitMqBundleConfiguration[$type])) {
                foreach ($rabbitMqBundleConfiguration[$type] as $name => $consumer) {
                    $consumerConfig = isset($config[$type]['individual'][$name]) ?
                        $config[$type]['individual'][$name] :
                        $config[$type]['general'];

                    $command = $config['commands'][$type];

                    if ('consumers' === $type && 'cli-consumer' === $consumerConfig['processor']) {
                        $command = $config['commands']['cli_consumer_invoker'];
                    }

                    $configuration[$type][$name] = $this->generateConfiguration(
                        $container,
                        $name,
                        $command,
                        $consumerConfig,
                        $consumer
                    );
                }
            }
        }

        $container->setParameter('mos_rabbitmq_cli_consumer.config', $configuration);
    }

    /**
     * @inheritdoc
     */
    public function prepend(ContainerBuilder $container)
    {
        foreach ($container->getExtensions() as $name => $extension) {
            switch ($name) {
                case 'old_sound_rabbit_mq':
                    // take over this bundle's configuration
                    $extensionConfig = $container->getExtensionConfig($name);

                    $container->setParameter('mos_old_sound_rabbit_mq.config', $extensionConfig[0]);
                    break;
            }
        }
    }

    /**
     * @param ContainerInterface $container
     * @param string             $name
     * @param string             $command
     * @param array              $config
     * @param array              $consumer
     *
     * @return array
     */
    private function generateConfiguration(ContainerInterface $container, $name, $command, array $config, array $consumer)
    {
        $connection = $this->getConnection($container, $consumer);

        return [
            'name' => $name,
            'processor' => $config['processor'],
            'messages' => $config['messages'],
            'callback' => $consumer['callback'],
            'connection' => [
                'host' => $connection['host'],
                'port' => $connection['port'],
                'user' => $connection['user'],
                'password' => $connection['password'],
                'vhost' => $connection['vhost'],
            ],
            'supervisor' => [
                'count' => $config['worker']['count'],
                'startsecs' => $config['worker']['startsecs'],
                'autorestart' => $config['worker']['autorestart'],
                'stopsignal' => $config['worker']['stopsignal'],
                'stopwaitsecs' => $config['worker']['stopwaitsecs'],
            ],
            'command' => [ // todo make this configurable at some point. as for now it's not important...
                'console' => '%kernel.root_dir%/../bin/console',
                'command' => isset($config['command']['command']) ? $config['command']['command'] : $command,
                'arguments' => [
                    '--env=%kernel.environment%',
                    '--app=%kernel.name%',
                ],
            ],
            'worker' => [
                'compression' => $config['compression'],
                'prefetch' => [
                    'count' => isset($consumer['qos_options']['prefetch_count']) ? $consumer['qos_options']['prefetch_count'] : 0,
                    'global' => isset($consumer['qos_options']['global']) ? $consumer['qos_options']['global'] : false,
                ],
                'exchange' => [
                    'name' => $consumer['exchange_options']['name'],
                    'type' => $consumer['exchange_options']['type'],
                    'autodelete' => isset($consumer['exchange_options']['auto_delete']) ? $consumer['exchange_options']['auto_delete'] : false,
                    'durable' => isset($consumer['exchange_options']['durable']) ? $consumer['exchange_options']['durable'] : true,
                ],
                'queue' => [
                    'name' => $consumer['queue_options']['name'],
                    'routing' => isset($consumer['queue_options']['routing_keys']) ? $consumer['queue_options']['routing_keys'] : null,
                ],
            ],
        ];
    }

    /**
     * @param ContainerInterface $container
     * @param array              $consumer
     *
     * @return array
     *
     * @throws InvalidConfigurationException
     */
    private function getConnection(ContainerInterface $container, array $consumer)
    {
        $connections = $container->getParameter('mos_old_sound_rabbit_mq.config')['connections'];
        $name = isset($consumer['connection']) ? $consumer['connection'] : 'default';

        if (isset($connections[$name])) {
            return $connections[$name];
        }

        throw new InvalidConfigurationException(sprintf(
            'Connection "%s" does not exist. Available connections: %s',
            $name,
            implode(', ', array_keys($connections))
        ));
    }
}
