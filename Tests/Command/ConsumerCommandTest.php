<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Tests\Command;

use MyOnlineStore\Bundle\RabbitMqManagerBundle\Command\ConsumerCommand;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Tester\CommandTester;

class ConsumerCommandTest extends BaseCommandTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->definition->expects($this->any())
            ->method('getOptions')
            ->willReturn([
                new InputOption('--callback', null, InputOption::VALUE_REQUIRED, 'Callback service name'),
            ]);

        $this->application->expects($this->once())->method('getHelperSet')->will($this->returnValue($this->helperSet));

        $this->command = new ConsumerCommand();
        $this->command->setApplication($this->application);
    }


    public function testExecute()
    {
        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasArgument('event'));
        $this->assertTrue($definition->getArgument('event')->isRequired());

        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasOption('callback'));
        $this->assertTrue($definition->getOption('callback')->isValueRequired());

        $this->container->expects($this->once())->method('get')->with('app.rabbitmq_consumer')->willReturn(
            $consumer = $this->getMock(ConsumerInterface::class)
        );

        $consumer->expects($this->once())->method('execute')->with(
            new AMQPMessage('content', [
                'foo' => 'bar',
            ])
        );

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            '--callback' => 'app.rabbitmq_consumer',
            'event' => base64_encode(gzcompress(json_encode([
                'body' => 'content',
                'properties' => [
                    'foo' => 'bar',
                ]
            ]))),
        ]);
    }
}
