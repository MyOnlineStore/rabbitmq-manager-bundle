<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Tests\DependencyInjection;

use MyOnlineStore\Bundle\RabbitMqManagerBundle\DependencyInjection\RabbitMqManagerExtension;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RabbitMqManagerExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ContainerBuilder
     */
    private $containerBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|RabbitMqManagerExtension
     */
    private $extension;

    protected function setUp()
    {
        $this->containerBuilder = $this->getMockBuilder(ContainerBuilder::class)->disableOriginalConstructor()->getMock();
        $this->extension = $this->getMock(RabbitMqManagerExtension::class, ['processConfiguration']);

        $this->extension->method('processConfiguration')->willReturn($this->getDefaultConfiguration());
    }

    public function testLoadWithRabbitMqConfiguration()
    {
        $this->containerBuilder->method('getParameter')->with('mos_old_sound_rabbit_mq.config')->willReturn([
            'connections' => [
                'default' => [
                    'host' => 'localhost',
                    'port' => 656,
                    'user' => 'guest',
                    'password' => 'guest',
                    'vhost' => '/',
                ],
            ],
            'consumers' => [
                'my_consumer' => [
                    'callback' => 'my_callback_service',
                    'exchange_options' => [
                        'name' => 'exchange-name',
                        'type' => 'direct',
                    ],
                    'queue_options' => [
                        'name' => 'queue-name',
                    ],
                ],
            ],
        ]);

        $this->setParameterWithYamlFileLoader('mos_rabbitmq_cli_consumer.config', [
            'path' => '%kernel.root_dir%/../var/supervisor/%kernel.name%',
            'consumers' => [
                'my_consumer' => [
                    'name' => 'my_consumer',
                    'processor' => 'bundle',
                    'messages' => 0,
                    'callback' => 'my_callback_service',
                    'connection' => [
                        'host' => 'localhost',
                        'port' => 656,
                        'user' => 'guest',
                        'password' => 'guest',
                        'vhost' => '/',
                    ],
                    'supervisor' => [
                        'count' => 1,
                        'startsecs' => 0,
                        'autorestart' => true,
                        'stopsignal' => 'INT',
                        'stopwaitsecs' => 60,
                    ],
                    'command' => [
                        'console' => '%kernel.root_dir%/../bin/console',
                        'command' => 'rabbitmq:consumer',
                        'arguments' => [
                            '--env=%kernel.environment%',
                            '--app=%kernel.name%',
                        ],
                    ],
                    'worker' => [
                        'compression' => true,
                        'prefetch' => [
                            'count' => 0,
                            'global' => false,
                        ],
                        'exchange' => [
                            'name' => 'exchange-name',
                            'type' => 'direct',
                            'autodelete' => false,
                            'durable' => true,
                        ],
                        'queue' => [
                            'name' => 'queue-name',
                            'routing' => null,
                        ],
                    ],
                ],
            ],
            'rpc_servers' => [],
        ]);

        $this->extension->load([], $this->containerBuilder);
    }

    public function testLoadWithRabbitMqConfigurationWithoutExistingConnection()
    {
        $this->setExpectedException(InvalidConfigurationException::class);

        $this->containerBuilder->method('getParameter')->with('mos_old_sound_rabbit_mq.config')->willReturn([
            'connections' => [
                'default' => [
                ],
            ],
            'consumers' => [
                'my_consumer' => [
                    'connection' => 'non_default',
                    'callback' => 'my_callback_service',
                ],
            ],
        ]);

        $this->extension->load([], $this->containerBuilder);
    }

    public function testLoadWithoutRabbitMqConfiguration()
    {
        $this->containerBuilder->expects($this->once())->method('getParameter')->with('mos_old_sound_rabbit_mq.config');
        $this->setParameterWithYamlFileLoader('mos_rabbitmq_cli_consumer.config', [
            'path' => '%kernel.root_dir%/../var/supervisor/%kernel.name%',
            'consumers' => [],
            'rpc_servers' => [],
        ]);

        $this->extension->load([], $this->containerBuilder);
    }

    public function testPrependNoMatchingService()
    {
        $this->containerBuilder->expects($this->once())->method('getExtensions')->willReturn([
            'foo' => 'bar',
            'hello' => 'world'
        ]);
        $this->containerBuilder->expects($this->never())->method('getExtensionConfig');

        $this->extension->prepend($this->containerBuilder);
    }

    public function testPrependMatchingService()
    {
        $this->containerBuilder->expects($this->once())->method('getExtensions')->willReturn([
            'foo' => 'bar',
            'hello' => 'world',
            'old_sound_rabbit_mq' => 'extension',
        ]);
        $this->containerBuilder->expects($this->once())->method('getExtensionConfig')
            ->with('old_sound_rabbit_mq')
            ->willReturn([['config' => 'entry']]);

        $this->containerBuilder->expects($this->once())->method('setParameter')->with(
            'mos_old_sound_rabbit_mq.config',
            ['config' => 'entry']
        );

        $this->extension->prepend($this->containerBuilder);
    }

    protected function setParameterWithYamlFileLoader($id, $value)
    {
        $this->containerBuilder->method('setParameter')->withConsecutive(
            ['myonlinestore_rabbitmq_manager.process_builder_factory.class'],
            ['myonlinestore_rabbitmq_manager.process_factory.class'],
            ['myonlinestore_rabbitmq_manager.config_generator.class'],
            ['phobetor_rabbitmq_supervisor.supervisor_service.class'],
            [$id, $value]);
    }

    protected function getDefaultConfiguration()
    {
        return [
            'path' => '%kernel.root_dir%/../var/supervisor/%kernel.name%',
            'commands' => [
                'cli_consumer_invoker' => 'rabbitmq-manager:consumer',
                'consumers' => 'rabbitmq:consumer',
                'multiple_consumers' => 'rabbitmq:multiple-consumer',
                'rpc_servers' => 'rabbitmq:rpc-server',
            ],
            'consumers' => [
                'general' => [
                    'processor' => 'bundle',
                    'messages' => 0,
                    'compression' => true,
                    'worker' => [
                        'count' => 1,
                        'startsecs' => 0,
                        'autorestart' => true,
                        'stopsignal' => 'INT',
                        'stopasgroup' => true,
                        'stopwaitsecs' => 60,
                    ],
                ],
                'individual' => [],
            ],
            'rpc_servers' => [
                'general' => [
                    'processor' => 'bundle',
                    'messages' => 0,
                    'compression' => true,
                    'worker' => [
                        'count' => 1,
                        'startsecs' => 0,
                        'autorestart' => true,
                        'stopsignal' => 'INT',
                        'stopasgroup' => true,
                        'stopwaitsecs' => 60,
                    ],
                ],
                'individual' => [],
            ]
        ];
    }
}
