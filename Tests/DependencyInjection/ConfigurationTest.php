<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Tests\DependencyInjection;

use MyOnlineStore\Bundle\RabbitMqManagerBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\NodeInterface;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NodeInterface
     */
    private $tree;

    protected function setUp()
    {
        $configuration = new Configuration();
        $builder = $configuration->getConfigTreeBuilder();

        $this->tree = $builder->buildTree();
    }

    public function testDefaults()
    {
        self::assertEquals([
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
        ], $this->tree->finalize([]));
    }

    public function testOverridesWithIndividuals()
    {
        self::assertEquals([
            'path' => '/foo/bar',
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
                'individual' => [
                    'my_consumer' => [
                        'processor' => 'cli-consumer',
                        'messages' => 1,
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
                ],
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
        ], $this->tree->finalize([
            'path' => '/foo/bar',
            'consumers' => [
                'individual' => [
                    'my_consumer' => [
                        'processor' => 'cli-consumer',
                        'messages' => 1,
                    ],
                ],
            ],
        ]));
    }
}
