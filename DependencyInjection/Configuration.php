<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This bundle uses the rabbit mq bundle's configuration
 */
final class Configuration implements ConfigurationInterface
{
    /**
     * @inheritdoc
     */
    public function getConfigTreeBuilder()
    {
        $tree = new TreeBuilder();

        $rootNode = $tree->root('rabbit_mq_manager');

        $rootNode
            ->children()
                ->scalarNode('path')->defaultValue('%kernel.root_dir%/../var/supervisor/%kernel.name%')->end()
            ->end();
        $this->addCommands($rootNode);
        $this->addConsumer($rootNode);
        $this->addRpcServer($rootNode);

        return $tree;
    }

    /**
     * Add commands configuration
     *
     * @param ArrayNodeDefinition $node
     */
    protected function addCommands(ArrayNodeDefinition $node)
    {
        $node
            ->fixXmlConfig('command')
            ->children()
                ->arrayNode('commands')
                ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('cli_consumer_invoker')->defaultValue('rabbitmq-manager:consumer')->end()
                        ->scalarNode('consumers')->defaultValue('rabbitmq:consumer')->end()
                        ->scalarNode('multiple_consumers')->defaultValue('rabbitmq:multiple-consumer')->end()
                        ->scalarNode('rpc_servers')->defaultValue('rabbitmq:rpc-server')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * Add general and individual consumer configuration
     *
     * @param ArrayNodeDefinition $node
     */
    protected function addConsumer(ArrayNodeDefinition $node)
    {
        $consumerChildren = $node
            ->children()
                ->arrayNode('consumers')
                ->addDefaultsIfNotSet()
                    ->children();

        $general = $consumerChildren
                        ->arrayNode('general');
        $this->addGeneralConsumerConfiguration($general);

        $individualPrototype = $consumerChildren
                        ->arrayNode('individual')
                            ->useAttributeAsKey('consumers')
                            ->prototype('array');
        $this->addGeneralConsumerConfiguration($individualPrototype);
    }

    /**
     * Add general and individual consumer configuration
     *
     * @param ArrayNodeDefinition $node
     */
    protected function addRpcServer(ArrayNodeDefinition $node)
    {
        $consumerChildren = $node
            ->children()
            ->arrayNode('rpc_servers')
            ->addDefaultsIfNotSet()
            ->children();

        $general = $consumerChildren
            ->arrayNode('general');
        $this->addGeneralConsumerConfiguration($general);

        $individualPrototype = $consumerChildren
            ->arrayNode('individual')
            ->useAttributeAsKey('rpc_servers')
            ->prototype('array');
        $this->addGeneralConsumerConfiguration($individualPrototype);
    }

    /**
     * Add consumer configuration
     *
     * @param ArrayNodeDefinition $node
     */
    protected function addGeneralConsumerConfiguration(ArrayNodeDefinition $node)
    {
        $node
        ->normalizeKeys(false)
        ->addDefaultsIfNotSet()
            ->children()
                ->enumNode('processor')
                    ->values(['bundle', 'cli-consumer'])
                    ->defaultValue('bundle')
                ->end()
                ->integerNode('messages')
                    ->min(0)
                    ->defaultValue(0)
                ->end()
                ->booleanNode('compression')
                    ->defaultTrue()
                ->end()
                ->arrayNode('worker')
                ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('count')
                            ->min(1)
                            ->defaultValue(1)
                        ->end()
                        ->integerNode('startsecs')
                            ->min(0)
                            ->defaultValue(0)
                        ->end()
                        ->booleanNode('autorestart')
                            ->defaultTrue()
                        ->end()
                        ->enumNode('stopsignal')
                            ->values(['TERM', 'INT', 'KILL'])
                            ->defaultValue('INT')
                        ->end()
                        ->booleanNode('stopasgroup')
                            ->defaultTrue()
                        ->end()
                        ->integerNode('stopwaitsecs')
                            ->min(0)
                            ->defaultValue(60)
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
