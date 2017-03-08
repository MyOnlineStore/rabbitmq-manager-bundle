<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Consumer;

use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Consumer\Section\Exchange;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Consumer\Section\Logs;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Consumer\Section\Prefetch;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Consumer\Section\Queue;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Consumer\Section\Rabbitmq;

final class ConsumerSectionFactory implements ConsumerSectionFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function createExchange(array $properties = [])
    {
        return new Exchange($properties);
    }

    /**
     * @inheritdoc
     */
    public function createLogs(array $properties = [])
    {
        return new Logs($properties);
    }

    /**
     * @inheritdoc
     */
    public function createPrefetch(array $properties = [])
    {
        return new Prefetch($properties);
    }

    /**
     * @inheritdoc
     */
    public function createQueue(array $properties = [])
    {
        return new Queue($properties);
    }

    /**
     * @inheritdoc
     */
    public function createRabbitmq(array $properties = [])
    {
        return new Rabbitmq($properties);
    }
}
