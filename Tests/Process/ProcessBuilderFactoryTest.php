<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Tests\Process;

use MyOnlineStore\Bundle\RabbitMqManagerBundle\Process\ProcessBuilderFactory;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Process\ProcessBuilderInterface;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Process\ProcessFactoryInterface;

class ProcessBuilderFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProcessBuilderFactory
     */
    private $factory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ProcessFactoryInterface
     */
    private $processFactory;

    protected function setUp()
    {
        $this->processFactory = $this->getMock(ProcessFactoryInterface::class);

        $this->factory = new ProcessBuilderFactory(
            $this->processFactory
        );
    }

    public function testCreate()
    {
        $this->assertInstanceOf(ProcessBuilderInterface::class, $this->factory->create());
    }
}
