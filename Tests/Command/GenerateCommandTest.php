<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Tests\Command;

use MyOnlineStore\Bundle\RabbitMqManagerBundle\Command\GenerateCommand;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Generator\RabbitMqConfigGeneratorInterface;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateCommandTest extends BaseCommandTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->definition->expects($this->any())->method('getArguments')->willReturn([]);
        $this->definition->expects($this->any())->method('getOptions')->willReturn([]);

        $this->command = new GenerateCommand();
        $this->command->setApplication($this->application);
    }


    public function testExecute()
    {
        $this->container->expects($this->once())->method('get')->with('myonlinestore_rabbitmq_manager.config_generator')->willReturn(
            $generator = $this->getMock(RabbitMqConfigGeneratorInterface::class)
        );

        $generator->expects($this->once())->method('generate');

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
    }
}
