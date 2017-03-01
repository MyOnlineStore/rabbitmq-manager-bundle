<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Consumer;

use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Consumer\Section\Exchange;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Consumer\Section\Logs;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Consumer\Section\Prefetch;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Consumer\Section\Queue;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Consumer\Section\Rabbitmq;

interface ConsumerSectionFactoryInterface
{
    /**
     * @param array $properties
     *
     * @return Exchange
     */
    public function createExchange(array $properties = []);

    /**
     * @param array $properties
     *
     * @return Logs
     */
    public function createLogs(array $properties = []);

    /**
     * @param array $properties
     *
     * @return Prefetch
     */
    public function createPrefetch(array $properties = []);

    /**
     * @param array $properties
     *
     * @return Queue
     */
    public function createQueue(array $properties = []);

    /**
     * @param array $properties
     *
     * @return Rabbitmq
     */
    public function createRabbitmq(array $properties = []);
}
