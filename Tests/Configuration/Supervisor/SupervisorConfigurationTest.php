<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Tests\Configuration\Supervisor;

use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Supervisor\SupervisorConfiguration;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Supervisor\SupervisorSectionFactoryInterface;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Process\ProcessBuilderFactoryInterface;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Process\ProcessBuilderInterface;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Process\ProcessInterface;
use Supervisor\Configuration\Section;

class SupervisorConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SupervisorSectionFactoryInterface
     */
    private $supervisorConfigurationSectionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ProcessBuilderFactoryInterface
     */
    private $processBuilderFactory;

    /**
     * @var SupervisorConfiguration
     */
    private $configuration;

    protected function setUp()
    {
        $this->supervisorConfigurationSectionFactory = $this->getMock(SupervisorSectionFactoryInterface::class);
        $this->processBuilderFactory = $this->getMock(ProcessBuilderFactoryInterface::class);

        $this->configuration = new SupervisorConfiguration(
            $this->supervisorConfigurationSectionFactory,
            $this->processBuilderFactory,
            '/my/path'
        );
    }

    public function testGenerate()
    {
        $this->supervisorConfigurationSectionFactory->expects($this->once())->method('createUnixHttpServer')->with([
            'file' => '/my/path/supervisord.sock',
            'chmod' => 700,
        ])->willReturn(
            $this->getMockBuilder(Section::class)->getMock()
        );

        $this->supervisorConfigurationSectionFactory->expects($this->once())->method('createSupervisord')->with([
            'logfile' => '/my/path/supervisord.log',
            'pidfile' => '/my/path/supervisord.pid',
        ])->willReturn(
            $this->getMockBuilder(Section::class)->getMock()
        );

        $this->supervisorConfigurationSectionFactory->expects($this->once())->method('createRpcInterface')->with(
            'supervisor',
            [
                'supervisor.rpcinterface_factory' => 'supervisor.rpcinterface:make_main_rpcinterface',
            ]
        )->willReturn(
            $this->getMockBuilder(Section::class)->getMock()
        );

        $this->supervisorConfigurationSectionFactory->expects($this->once())->method('createSupervisorctl')->with([
            'serverurl' => 'unix:///my/path/supervisord.sock',
        ])->willReturn(
            $this->getMockBuilder(Section::class)->getMock()
        );

        $this->configuration->generate();
    }

    public function testGenerateProgram()
    {
        $this->supervisorConfigurationSectionFactory->expects($this->once())->method('createProgram')->with(
            'name',
            [
                'command' => 'command',
            ]
        );

        $this->configuration->generateProgram('name', [
            'command' => 'command'
        ]);
    }

    public function testGetBundlePropertiesWithoutRoute()
    {
        $this->processBuilderFactory->expects($this->once())->method('create')->willReturn(
            $processBuilder = $this->getMock(ProcessBuilderInterface::class)
        );

        $processBuilder->expects($this->once())->method('setPrefix')->with(['php']);

        $processBuilder->expects($this->exactly(6))->method('add')->withConsecutive(
            ['bin/console'],
            ['--foo=bar'],
            ['--hello=world'],
            ['--messages=5'],
            ['rabbitmq:consumer'],
            ['my_consumer']
        );

        $processBuilder->expects($this->once())->method('getProcess')->willReturn(
            $process = $this->getMock(ProcessInterface::class)
        );

        $process->expects($this->once())->method('getCommandLine')->willReturn(
            'php bin/console --foo=bar --hello=world --messages=5 rabbitmq:consumer my_consumer'
        );

        $properties = $this->configuration->getBundleProperties([
            'name' => 'my_consumer',
            'messages' => 5,
            'command' => [
                'console' => 'bin/console',
                'command' => 'rabbitmq:consumer',
                'arguments' => [
                    '--foo=bar',
                    '--hello=world',
                ],
            ],
            'supervisor' => [
                'count' => 4,
                'startsecs' => 0,
                'autorestart' => 1,
                'stopsignal' => 'INT',
                'stopwaitsecs' => 60,
            ],
        ], null);

        $this->assertEquals([
            'command' => 'php bin/console --foo=bar --hello=world --messages=5 rabbitmq:consumer my_consumer',
            'process_name' => '%(program_name)s%(process_num)02d',
            'numprocs' => 4,
            'startsecs' => 0,
            'autorestart' => 1,
            'stopsignal' => 'INT',
            'stopwaitsecs' => 60,
        ], $properties);
    }

    public function testGetBundlePropertiesWithRoute()
    {
        $this->processBuilderFactory->expects($this->once())->method('create')->willReturn(
            $processBuilder = $this->getMock(ProcessBuilderInterface::class)
        );

        $processBuilder->expects($this->once())->method('setPrefix')->with(['php']);

        $processBuilder->expects($this->exactly(7))->method('add')->withConsecutive(
            ['bin/console'],
            ['--foo=bar'],
            ['--hello=world'],
            ['--messages=5'],
            ['--route=my-route'],
            ['rabbitmq:consumer'],
            ['my_consumer']
        );

        $processBuilder->expects($this->once())->method('getProcess')->willReturn(
            $process = $this->getMock(ProcessInterface::class)
        );

        $process->expects($this->once())->method('getCommandLine')->willReturn(
            'php bin/console --foo=bar --hello=world --messages=5 --route=my-route rabbitmq:consumer my_consumer'
        );

        $properties = $this->configuration->getBundleProperties([
            'name' => 'my_consumer',
            'messages' => 5,
            'command' => [
                'console' => 'bin/console',
                'command' => 'rabbitmq:consumer',
                'arguments' => [
                    '--foo=bar',
                    '--hello=world',
                ],
            ],
            'supervisor' => [
                'count' => 4,
                'startsecs' => 0,
                'autorestart' => 1,
                'stopsignal' => 'INT',
                'stopwaitsecs' => 60,
            ],
        ], 'my-route');

        $this->assertEquals([
            'command' => 'php bin/console --foo=bar --hello=world --messages=5 --route=my-route rabbitmq:consumer my_consumer',
            'process_name' => '%(program_name)s%(process_num)02d',
            'numprocs' => 4,
            'startsecs' => 0,
            'autorestart' => 1,
            'stopsignal' => 'INT',
            'stopwaitsecs' => 60,
        ], $properties);
    }

    public function testGetConsumerProperties()
    {
        $this->processBuilderFactory->expects($this->exactly(2))->method('create')->willReturnOnConsecutiveCalls(
            $processBuilder = $this->getMock(ProcessBuilderInterface::class),
            $consumerProcessBuilder = $this->getMock(ProcessBuilderInterface::class)
        );

        $processBuilder->expects($this->once())->method('setPrefix')->with(['php']);
        $processBuilder->expects($this->exactly(5))->method('add')->withConsecutive(
            ['bin/console'],
            ['--foo=bar'],
            ['--hello=world'],
            ['rabbitmq-manager:consumer'],
            ['my_consumer']
        );
        $processBuilder->expects($this->once())->method('getProcess')->willReturn(
            $process = $this->getMock(ProcessInterface::class)
        );

        $process->expects($this->once())->method('getCommandLine')->willReturn(
            'php bin/console --foo=bar --hello=world rabbitmq-manager:consumer my_consumer'
        );

        $consumerProcessBuilder->expects($this->once())->method('setPrefix')->with(['rabbitmq-cli-consumer']);
        $consumerProcessBuilder->expects($this->exactly(4))->method('add')->withConsecutive(
            ['--strict-exit-code'],
            ['--include'],
            ['--configuration=/my/config.conf'],
            ['--executable=php bin/console --foo=bar --hello=world rabbitmq-manager:consumer my_consumer']
        );
        $consumerProcessBuilder->expects($this->once())->method('getProcess')->willReturn(
            $consumerProcess = $this->getMock(ProcessInterface::class)
        );

        $consumerProcess->expects($this->once())->method('getCommandLine')->willReturn(
            'rabbitmq-cli-consumer --strict-exit-code --include --configuration=/my/config.conf --executable="php bin/console --foo=bar --hello=world rabbitmq-manager:consumer my_consumer"'
        );

        $properties = $this->configuration->getConsumerProperties([
            'name' => 'my_consumer',
            'messages' => 5,
            'command' => [
                'console' => 'bin/console',
                'command' => 'rabbitmq-manager:consumer',
                'arguments' => [
                    '--foo=bar',
                    '--hello=world',
                ],
            ],
            'supervisor' => [
                'count' => 4,
                'startsecs' => 0,
                'autorestart' => 1,
                'stopsignal' => 'INT',
                'stopwaitsecs' => 60,
            ],
        ], '/my/config.conf');

        $this->assertEquals([
            'command' => 'rabbitmq-cli-consumer --strict-exit-code --include --configuration=/my/config.conf --executable="php bin/console --foo=bar --hello=world rabbitmq-manager:consumer my_consumer"',
            'process_name' => '%(program_name)s%(process_num)02d',
            'numprocs' => 4,
            'startsecs' => 0,
            'autorestart' => 1,
            'stopsignal' => 'INT',
            'stopwaitsecs' => 60,
        ], $properties);
    }
}
