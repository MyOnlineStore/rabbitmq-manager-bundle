<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Tests\Process;

use MyOnlineStore\Bundle\RabbitMqManagerBundle\Process\ProcessFactory;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Process\ProcessInterface;
use Symfony\Component\Process\Process;

class ProcessFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProcessFactory
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = new ProcessFactory();
    }

    public function testCreate()
    {
        $this->assertInstanceOf(ProcessInterface::class, $this->factory->create('cli-command'));
    }

    public function testCreateFromProcess()
    {
        $symfonyProcess = new Process('cli-command');

        $this->assertNotInstanceOf(ProcessInterface::class, $symfonyProcess);

        $process = $this->factory->createFromProcess($symfonyProcess);
        $this->assertInstanceOf(ProcessInterface::class, $process);
        $this->assertEquals('cli-command', $process->getCommandLine());
    }
}
