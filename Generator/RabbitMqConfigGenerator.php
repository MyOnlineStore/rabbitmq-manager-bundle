<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Generator;

use MyOnlineStore\Bundle\RabbitMqManagerBundle\Process\ProcessBuilderFactoryInterface;
use Supervisor\Configuration\Section\RpcInterface;
use Supervisor\Configuration\Section\Supervisorctl;
use Supervisor\Configuration\Section\UnixHttpServer;
use Supervisor\Configuration\Configuration;
use Supervisor\Configuration\Section\Supervisord;
use Supervisor\Configuration\Section\Program;
use Indigo\Ini\Renderer;
use Symfony\Component\Templating\EngineInterface;

class RabbitMqConfigGenerator implements RabbitMqConfigGeneratorInterface
{
    /**
     * @var array
     */
    private $config;
    /**
     * @var ProcessBuilderFactoryInterface
     */
    private $processBuilderFactory;
    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @param ProcessBuilderFactoryInterface $processBuilderFactory
     * @param EngineInterface                $templating
     * @param array                          $config
     */
    public function __construct(ProcessBuilderFactoryInterface $processBuilderFactory, EngineInterface $templating, array $config)
    {
        $this->config = $config;
        $this->processBuilderFactory = $processBuilderFactory;
        $this->templating = $templating;
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        if (!is_dir($this->config['path']) && !mkdir($this->config['path'], 0755, true)) {
            throw new \RuntimeException(sprintf(
                'path "%s" could not be created',
                $this->config['path']
            ));
        }

        $configuration = new Configuration();
        $renderer = new Renderer();

        $configuration->addSection(new UnixHttpServer([
            'file' => sprintf('%s/supervisord.sock', $this->config['path']),
            'chmod' => 700,
        ]));

        $configuration->addSection(new Supervisord([
            'logfile' => sprintf('%s/supervisord.log', $this->config['path']),
            'pidfile' => sprintf('%s/supervisord.pid', $this->config['path']),
        ]));

        $configuration->addSection(new RpcInterface('supervisor', [
            'supervisor.rpcinterface_factory' => 'supervisor.rpcinterface:make_main_rpcinterface',
        ]));

        $configuration->addSection(new Supervisorctl([
            'serverurl' => sprintf('unix://%s/supervisord.sock', $this->config['path']),
        ]));

        foreach (['consumers', 'rpc_servers'] as $type) {
            foreach ($this->config[$type] as $name => $consumer) {
                if (!isset($consumer['worker']['queue']['routing'])) {
                    $configuration->addSection($this->getProgramSection(
                        sprintf('%s_%s', $type, $name),
                        $this->config['path'],
                        $consumer
                    ));

                    continue;
                }

                foreach ($consumer['worker']['queue']['routing'] as $index => $route) {
                    $configuration->addSection($this->getProgramSection(
                        sprintf('%s_%s_%s', $type, $name, $index),
                        $this->config['path'],
                        $consumer,
                        $route
                    ));
                }
            }
        }

        file_put_contents(
            sprintf('%s/%s', $this->config['path'], 'supervisord.conf'),
            $renderer->render($configuration->toArray())
        );
    }

    /**
     * @param string $name
     * @param string $path
     * @param array  $consumer
     * @param null   $route
     *
     * @return Program
     */
    public function getProgramSection($name, $path, array $consumer, $route = null)
    {
        $processBuilder = $this->processBuilderFactory->create();
        $processBuilder->setPrefix(['php']);
        $processBuilder->add($consumer['command']['console']);

        foreach ($consumer['command']['arguments'] as $argument) {
            $processBuilder->add($argument);
        }

        if ('cli-consumer' === $consumer['processor']) {
            // write additional cli-consumer config
            file_put_contents(
                $consumerConfiguration = sprintf('%s/%s.conf', $path, $name),
                $this->templating->render('RabbitMqManagerBundle:Supervisor:consumer.conf.twig', [
                    'path' => $path,
                    'routing_key' => $route,
                    'consumer' => $consumer,
                ])
            );

            $processBuilder->add($consumer['command']['command']);
            $processBuilder->add($consumer['name']);

            $consumerProcessBuilder = $this->processBuilderFactory->create();
            $consumerProcessBuilder->setPrefix(['rabbitmq-cli-consumer']);
            $consumerProcessBuilder->add('--strict-exit-code');
            $consumerProcessBuilder->add('--include');
            $consumerProcessBuilder->add(sprintf('--configuration=%s', $consumerConfiguration));
            $consumerProcessBuilder->add(sprintf('--executable=%s', $processBuilder->getProcess()->getCommandLine()));

            $process = $consumerProcessBuilder->getProcess();
        } else {
            $processBuilder->add(sprintf('--messages=%s', $consumer['messages']));

            if (null !== $route) {
                $processBuilder->add(sprintf('--route=%s', $route));
            }

            $processBuilder->add($consumer['command']['command']);
            $processBuilder->add($consumer['name']);
            $process = $processBuilder->getProcess();
        }

        return new Program($name, [
            'command' => $process->getCommandLine(),
            'process_name' => '%(program_name)s%(process_num)02d',
            'numprocs' => $consumer['supervisor']['count'],
            'startsecs' => $consumer['supervisor']['startsecs'],
            'autorestart' => $consumer['supervisor']['autorestart'],
            'stopsignal' => $consumer['supervisor']['stopsignal'],
            'stopwaitsecs' => $consumer['supervisor']['stopwaitsecs'],
        ]);
    }
}
