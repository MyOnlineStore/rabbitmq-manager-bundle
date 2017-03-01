<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Tests\Configuration\Consumer;

use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Consumer\ConsumerConfiguration;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Consumer\ConsumerSectionFactoryInterface;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Section\SectionCollection;
use Supervisor\Configuration\Section;

class ConsumerConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ConsumerSectionFactoryInterface
     */
    private $consumerConfigurationSectionFactory;

    /**
     * @var ConsumerConfiguration
     */
    private $configuration;

    protected function setUp()
    {
        $this->consumerConfigurationSectionFactory = $this->getMock(ConsumerSectionFactoryInterface::class);

        $this->configuration = new ConsumerConfiguration(
            $this->consumerConfigurationSectionFactory,
            '/my/path'
        );
    }

    public function testGenerateMinimumConfiguration()
    {
        $this->consumerConfigurationSectionFactory->expects($this->once())->method('createRabbitmq')->with([
            'host' => 'localhost',
            'port' => 45345,
            'username' => 'username',
            'password' => 'password',
            'vhost' => '/',
            'queue' => 'my-queue',
            'compression' => false,
        ])->willReturn(
            $this->getMockBuilder(Section::class)->getMock()
        );

        $this->consumerConfigurationSectionFactory->expects($this->never())->method('createPrefetch');

        $this->consumerConfigurationSectionFactory->expects($this->never())->method('createExchange');

        $this->consumerConfigurationSectionFactory->expects($this->once())->method('createQueue')->with([
            'routingkey' => 'my-route',
        ])->willReturn(
            $this->getMockBuilder(Section::class)->getMock()
        );

        $this->consumerConfigurationSectionFactory->expects($this->once())->method('createLogs')->with([
            'info' => '/my/path/consumer.log',
            'error' => '/my/path/consumer.err',
        ])->willReturn(
            $this->getMockBuilder(Section::class)->getMock()
        );

        $configuration = $this->configuration->generate([
            'connection' => [
                'host' => 'localhost',
                'port' => 45345,
                'user' => 'username',
                'password' => 'password',
                'vhost' => '/',
            ],
            'worker' => [
                'compression' => false,
                'queue' => [
                    'name' => 'my-queue',
                ],
                'prefetch' => [
                    'count' => 0,
                    'global' => false,
                ],
            ],
        ], 'my-route');

        $this->assertInstanceOf(SectionCollection::class, $configuration);
    }

    public function testGenerateWithRoute()
    {
        $this->consumerConfigurationSectionFactory->expects($this->once())->method('createRabbitmq')->with([
            'host' => 'localhost',
            'port' => 45345,
            'username' => 'username',
            'password' => 'password',
            'vhost' => '/',
            'queue' => 'my-queue',
            'compression' => false,
        ])->willReturn(
            $this->getMockBuilder(Section::class)->getMock()
        );

        $this->consumerConfigurationSectionFactory->expects($this->once())->method('createPrefetch')->with([
            'count' => 5,
            'global' => false,
        ])->willReturn(
            $this->getMockBuilder(Section::class)->getMock()
        );

        $this->consumerConfigurationSectionFactory->expects($this->once())->method('createExchange')->with([
            'name' => 'my-exchange',
            'type' => 'topic',
            'durable' => false,
            'autodelete' => true,
        ])->willReturn(
            $this->getMockBuilder(Section::class)->getMock()
        );

        $this->consumerConfigurationSectionFactory->expects($this->once())->method('createQueue')->with([
            'routingkey' => 'my-route',
        ])->willReturn(
            $this->getMockBuilder(Section::class)->getMock()
        );

        $this->consumerConfigurationSectionFactory->expects($this->once())->method('createLogs')->with([
            'info' => '/my/path/consumer.log',
            'error' => '/my/path/consumer.err',
        ])->willReturn(
            $this->getMockBuilder(Section::class)->getMock()
        );

        $configuration = $this->configuration->generate([
            'connection' => [
                'host' => 'localhost',
                'port' => 45345,
                'user' => 'username',
                'password' => 'password',
                'vhost' => '/',
            ],
            'worker' => [
                'compression' => false,
                'queue' => [
                    'name' => 'my-queue',
                ],
                'prefetch' => [
                    'count' => 5,
                    'global' => false,
                ],
                'exchange' => [
                    'name' => 'my-exchange',
                    'type' => 'topic',
                    'durable' => false,
                    'autodelete' => true,
                ],
            ],
        ], 'my-route');

        $this->assertInstanceOf(SectionCollection::class, $configuration);
    }

    public function testGenerateWithoutRoute()
    {
        $this->consumerConfigurationSectionFactory->expects($this->once())->method('createRabbitmq')->with([
            'host' => 'localhost',
            'port' => 45345,
            'username' => 'username',
            'password' => 'password',
            'vhost' => '/',
            'queue' => 'my-queue',
            'compression' => false,
        ])->willReturn(
            $this->getMockBuilder(Section::class)->getMock()
        );

        $this->consumerConfigurationSectionFactory->expects($this->once())->method('createPrefetch')->with([
            'count' => 5,
            'global' => false,
        ])->willReturn(
            $this->getMockBuilder(Section::class)->getMock()
        );

        $this->consumerConfigurationSectionFactory->expects($this->once())->method('createExchange')->with([
            'name' => 'my-exchange',
            'type' => 'topic',
            'durable' => false,
            'autodelete' => true,
        ])->willReturn(
            $this->getMockBuilder(Section::class)->getMock()
        );

        $this->consumerConfigurationSectionFactory->expects($this->never())->method('createQueue');

        $this->consumerConfigurationSectionFactory->expects($this->once())->method('createLogs')->with([
            'info' => '/my/path/consumer.log',
            'error' => '/my/path/consumer.err',
        ])->willReturn(
            $this->getMockBuilder(Section::class)->getMock()
        );

        $configuration = $this->configuration->generate([
            'connection' => [
                'host' => 'localhost',
                'port' => 45345,
                'user' => 'username',
                'password' => 'password',
                'vhost' => '/',
            ],
            'worker' => [
                'compression' => false,
                'queue' => [
                    'name' => 'my-queue',
                ],
                'prefetch' => [
                    'count' => 5,
                    'global' => false,
                ],
                'exchange' => [
                    'name' => 'my-exchange',
                    'type' => 'topic',
                    'durable' => false,
                    'autodelete' => true,
                ],
            ],
        ]);

        $this->assertInstanceOf(SectionCollection::class, $configuration);
    }
}
