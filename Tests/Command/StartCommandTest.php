<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Tests\Command;

use MyOnlineStore\Bundle\RabbitMqManagerBundle\Command\StartCommand;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Services\RabbitMqSupervisor;
use Symfony\Component\Console\Tester\CommandTester;

class StartCommandTest extends BaseCommandTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->definition->expects($this->any())->method('getArguments')->willReturn([]);
        $this->definition->expects($this->any())->method('getOptions')->willReturn([]);

        $this->command = new StartCommand();
        $this->command->setApplication($this->application);
    }

    public function testExecuteWithoutGenerate()
    {
        $this->container->expects($this->once())->method('get')->with('phobetor_rabbitmq_supervisor')->willReturn(
            $service = $this->getMockBuilder(RabbitMqSupervisor::class)->disableOriginalConstructor()->getMock()
        );

        $service->expects($this->never())->method('generate');
        $service->expects($this->once())->method('start');

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
    }

    public function testExecute()
    {
        $this->container->expects($this->once())->method('get')->with('phobetor_rabbitmq_supervisor')->willReturn(
            $service = $this->getMockBuilder(RabbitMqSupervisor::class)->disableOriginalConstructor()->getMock()
        );

        $service->expects($this->once())->method('generate');
        $service->expects($this->once())->method('start');

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            '--generate' => true,
        ]);
    }
}
