<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Tests\Command;

use MyOnlineStore\Bundle\RabbitMqManagerBundle\Command\StopCommand;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Supervisor\SupervisorInterface;
use Symfony\Component\Console\Tester\CommandTester;

class StopCommandTest extends BaseCommandTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->definition->expects($this->any())->method('getArguments')->willReturn([]);
        $this->definition->expects($this->any())->method('getOptions')->willReturn([]);

        $this->command = new StopCommand();
        $this->command->setApplication($this->application);
    }

    public function testExecuteWithoutGenerate()
    {
        $this->container->expects($this->once())->method('get')->with('myonlinestore_rabbitmq_manager.supervisor')->willReturn(
            $supervisor = $this->getMock(SupervisorInterface::class)
        );

        $supervisor->expects($this->once())->method('stop');

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
    }
}
