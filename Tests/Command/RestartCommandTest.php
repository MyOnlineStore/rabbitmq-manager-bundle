<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Tests\Command;

use MyOnlineStore\Bundle\RabbitMqManagerBundle\Command\RestartCommand;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Generator\RabbitMqConfigGeneratorInterface;
use Symfony\Component\Console\Tester\CommandTester;

class RestartCommandTest extends BaseCommandTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->definition->expects($this->any())->method('getArguments')->willReturn([]);
        $this->definition->expects($this->any())->method('getOptions')->willReturn([]);

        $this->command = new RestartCommand();
        $this->command->setApplication($this->application);
    }

    public function testExecuteWithoutGenerate()
    {
        $this->container->expects($this->once())->method('get')->with('myonlinestore_rabbitmq_manager.supervisor')->willReturn(
            $supervisor = $this->getMock(RabbitMqConfigGeneratorInterface::class)
        );

        $supervisor->expects($this->never())->method('generate');
        $supervisor->expects($this->once())->method('stop');
        $supervisor->expects($this->once())->method('start');

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
    }

    public function testExecute()
    {
        $this->container->expects($this->once())->method('get')->with('myonlinestore_rabbitmq_manager')->willReturn(
            $service = $this->getMock(RabbitMqManagerInterface::class)
        );

        $service->expects($this->once())->method('generate');
        $service->expects($this->once())->method('stop');
        $service->expects($this->once())->method('start');

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            '--generate' => true,
        ]);
    }
}
