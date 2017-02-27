<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Tests\Process;

use MyOnlineStore\Bundle\RabbitMqManagerBundle\Process\ProcessBuilder;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Process\ProcessFactoryInterface;

class ProcessBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProcessBuilder
     */
    private $builder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ProcessFactoryInterface
     */
    private $processFactory;

    protected function setUp()
    {
        $this->processFactory = $this->getMock(ProcessFactoryInterface::class);

        $this->builder = new ProcessBuilder(
            ['cli-command'],
            $this->processFactory
        );
    }

    public function testGetProcess()
    {
        $this->processFactory->expects($this->once())->method('createFromProcess');

        $this->builder->getProcess();
    }
}
