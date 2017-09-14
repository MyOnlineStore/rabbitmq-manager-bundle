<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Tests\Command;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\Stream\StreamInterface;
use League\Flysystem\FilesystemInterface;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Command\DownloadManagementToolCommand;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Command\RestartCommand;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Generator\RabbitMqConfigGeneratorInterface;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Supervisor\SupervisorInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

final class DownloadManagementToolCommandTest extends BaseCommandTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->definition->expects($this->any())->method('getArguments')->willReturn([]);
        $this->definition->expects($this->any())->method('getOptions')->willReturn([]);

        $this->command = new DownloadManagementToolCommand();
        $this->command->setApplication($this->application);
    }

    public function testExecute()
    {
        $this->container->expects(self::exactly(2))->method('get')
            ->withConsecutive(
                ['myonlinestore_rabbitmq_manager.http_client'],
                ['myonlinestore_rabbitmq_manager.filesystem']
            )
            ->willReturnOnConsecutiveCalls(
                $client = $this->getMock(ClientInterface::class),
                $filesystem = $this->getMock(FilesystemInterface::class)
            );

        $client->expects(self::once())->method('createRequest')
            ->with(
                'GET',
                'http://remotehost:6789/cli/rabbitmqadmin'
            )
            ->willReturn(
                $request = $this->getMock(RequestInterface::class)
            );

        $client->expects(self::once())->method('send')
            ->with($request)
            ->willReturn(
                $response = $this->getMock(ResponseInterface::class)
            );

        $response->expects(self::once())->method('getBody')
            ->willReturn(
                $body = $this->getMock(StreamInterface::class)
            );

        $body->expects(self::once())->method('getContents')
            ->willReturn('some content');

        $filesystem->expects(self::once())->method('put')
            ->with(
                'fileadmin',
                'some content'
            );

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            'filename' => 'fileadmin',
            '--hostname' => 'remotehost',
            '--port' => '6789',
        ]);
    }
}
