<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Tests\Configuration\Consumer;

use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Consumer\ConsumerSectionFactory;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Consumer\Section\Exchange;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Consumer\Section\Logs;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Consumer\Section\Prefetch;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Consumer\Section\Queue;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Consumer\Section\Rabbitmq;

class ConsumerSectionFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConsumerSectionFactory
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = new ConsumerSectionFactory();
    }

    public function testCreateExchange()
    {
        $this->assertInstanceOf(Exchange::class, $this->factory->createExchange([]));
    }

    public function testCreateLogs()
    {
        $this->assertInstanceOf(Logs::class, $this->factory->createLogs([]));
    }

    public function testCreatePrefetch()
    {
        $this->assertInstanceOf(Prefetch::class, $this->factory->createPrefetch([]));
    }

    public function testCreateQueue()
    {
        $this->assertInstanceOf(Queue::class, $this->factory->createQueue([]));
    }

    public function testCreateRabbitmq()
    {
        $this->assertInstanceOf(Rabbitmq::class, $this->factory->createRabbitmq([]));
    }
}
