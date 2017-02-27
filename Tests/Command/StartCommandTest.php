<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Tests\Command;

use MyOnlineStore\Bundle\RabbitMqManagerBundle\Command\StartCommand;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Exception\Supervisor\SupervisorAlreadyRunningException;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Generator\RabbitMqConfigGeneratorInterface;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Supervisor\SupervisorInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

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
        $this->container->expects($this->once())->method('get')->with('myonlinestore_rabbitmq_manager.supervisor')->willReturn(
            $supervisor = $this->getMock(SupervisorInterface::class)
        );

        $supervisor->expects($this->once())->method('start');

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
    }

    public function testExecute()
    {
        $generator = $this->getMock(RabbitMqConfigGeneratorInterface::class);
        $supervisor = $this->getMock(SupervisorInterface::class);

        $this->container->method('get')->willReturnCallback(function ($service) use ($generator, $supervisor) {
            switch ($service) {
                case 'myonlinestore_rabbitmq_manager.config_generator':
                    return $generator;
                case 'myonlinestore_rabbitmq_manager.supervisor':
                    return $supervisor;
            }

            throw new ServiceNotFoundException($service);
        });

        $generator->expects($this->once())->method('generate');
        $supervisor->expects($this->once())->method('start');

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            '--generate' => true,
        ]);
    }

    public function testExecuteAlreadyRunning()
    {
        $generator = $this->getMock(RabbitMqConfigGeneratorInterface::class);
        $supervisor = $this->getMock(SupervisorInterface::class);

        $this->container->method('get')->willReturnCallback(function ($service) use ($generator, $supervisor) {
            switch ($service) {
                case 'myonlinestore_rabbitmq_manager.config_generator':
                    return $generator;
                case 'myonlinestore_rabbitmq_manager.supervisor':
                    return $supervisor;
            }

            throw new ServiceNotFoundException($service);
        });

        $generator->expects($this->once())->method('generate');
        $supervisor->expects($this->once())->method('start')->willThrowException(
            new SupervisorAlreadyRunningException()
        );

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            '--generate' => true,
        ]);
    }
}
