<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Supervisor;

use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Section\SectionCollection;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Process\ProcessBuilderFactoryInterface;
use Supervisor\Configuration\Section\Program;

class SupervisorConfiguration
{
    /**
     * @var SupervisorSectionFactoryInterface
     */
    private $sectionFactory;

    /**
     * @var ProcessBuilderFactoryInterface
     */
    private $processBuilderFactory;

    /**
     * @var string
     */
    private $path;

    /**
     * @param SupervisorSectionFactoryInterface $sectionFactory
     * @param ProcessBuilderFactoryInterface    $processBuilderFactory
     * @param string                            $path
     */
    public function __construct(
        SupervisorSectionFactoryInterface $sectionFactory,
        ProcessBuilderFactoryInterface $processBuilderFactory,
        $path
    ) {
        $this->sectionFactory = $sectionFactory;
        $this->processBuilderFactory = $processBuilderFactory;
        $this->path = $path;
    }

    public function generate()
    {
        $sections = new SectionCollection();

        $sections->addSection(
            $this->sectionFactory->createUnixHttpServer(
                [
                    'file' => sprintf('%s/supervisord.sock', $this->path),
                    'chmod' => 700,
                ]
            )
        );

        $sections->addSection(
            $this->sectionFactory->createSupervisord(
                [
                    'logfile' => sprintf('%s/supervisord.log', $this->path),
                    'pidfile' => sprintf('%s/supervisord.pid', $this->path),
                ]
            )
        );

        $sections->addSection(
            $this->sectionFactory->createRpcInterface(
                'supervisor',
                [
                    'supervisor.rpcinterface_factory' => 'supervisor.rpcinterface:make_main_rpcinterface',
                ]
            )
        );

        $sections->addSection(
            $this->sectionFactory->createSupervisorctl(
                [
                    'serverurl' => sprintf('unix://%s/supervisord.sock', $this->path),
                ]
            )
        );

        return $sections;
    }

    /**
     * @param string $name
     * @param array  $properties
     *
     * @return Program
     */
    public function generateProgram($name, array $properties = [])
    {
        return $this->sectionFactory->createProgram($name, $properties);
    }

    /**
     * @param array       $consumer
     * @param null|string $route
     *
     * @return array
     */
    public function getBundleProperties(array $consumer, $route = null)
    {
        $processBuilder = $this->processBuilderFactory->create();
        $processBuilder->setPrefix(['php']);
        $processBuilder->add($consumer['command']['console']);

        foreach ($consumer['command']['arguments'] as $argument) {
            $processBuilder->add($argument);
        }

        $processBuilder->add(sprintf('--messages=%s', $consumer['messages']));

        if (null !== $route) {
            $processBuilder->add(sprintf('--route=%s', $route));
        }

        $processBuilder->add($consumer['command']['command']);
        $processBuilder->add($consumer['name']);
        $process = $processBuilder->getProcess();

        return [
            'command' => $process->getCommandLine(),
            'process_name' => '%(program_name)s%(process_num)02d',
            'numprocs' => $consumer['supervisor']['count'],
            'startsecs' => $consumer['supervisor']['startsecs'],
            'autorestart' => $consumer['supervisor']['autorestart'],
            'stopsignal' => $consumer['supervisor']['stopsignal'],
            'stopwaitsecs' => $consumer['supervisor']['stopwaitsecs'],
        ];
    }

    /**
     * @param array  $consumer
     * @param string $consumerConfiguration
     *
     * @return array
     */
    public function getConsumerProperties(array $consumer, $consumerConfiguration)
    {
        $processBuilder = $this->processBuilderFactory->create();
        $processBuilder->setPrefix(['php']);
        $processBuilder->add($consumer['command']['console']);

        foreach ($consumer['command']['arguments'] as $argument) {
            $processBuilder->add($argument);
        }

        $processBuilder->add($consumer['command']['command']);
        $processBuilder->add($consumer['name']);

        $consumerProcessBuilder = $this->processBuilderFactory->create();
        $consumerProcessBuilder->setPrefix(['rabbitmq-cli-consumer']);
        $consumerProcessBuilder->add('--strict-exit-code');
        $consumerProcessBuilder->add('--include');
        $consumerProcessBuilder->add(sprintf('--configuration=%s', $consumerConfiguration));
        $consumerProcessBuilder->add(sprintf('--executable=%s', $processBuilder->getProcess()->getCommandLine()));

        $process = $consumerProcessBuilder->getProcess();

        return [
            'command' => $process->getCommandLine(),
            'process_name' => '%(program_name)s%(process_num)02d',
            'numprocs' => $consumer['supervisor']['count'],
            'startsecs' => $consumer['supervisor']['startsecs'],
            'autorestart' => $consumer['supervisor']['autorestart'],
            'stopsignal' => $consumer['supervisor']['stopsignal'],
            'stopwaitsecs' => $consumer['supervisor']['stopwaitsecs'],
        ];
    }
}
