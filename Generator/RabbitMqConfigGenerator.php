<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Generator;

use Indigo\Ini\Renderer;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Consumer\ConsumerConfiguration;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Supervisor\SupervisorConfiguration;

class RabbitMqConfigGenerator implements RabbitMqConfigGeneratorInterface
{
    /**
     * @var SupervisorConfiguration
     */
    private $supervisorConfiguration;

    /**
     * @var ConsumerConfiguration
     */
    private $consumerConfiguration;

    /**
     * @var Renderer
     */
    private $renderer;

    /**
     * @var array
     */
    private $config;

    /**
     * @param SupervisorConfiguration $supervisorConfiguration
     * @param ConsumerConfiguration   $consumerConfiguration
     * @param Renderer                $renderer
     * @param array                   $config
     */
    public function __construct(
        SupervisorConfiguration $supervisorConfiguration,
        ConsumerConfiguration $consumerConfiguration,
        Renderer $renderer,
        array $config
    ) {
        $this->supervisorConfiguration = $supervisorConfiguration;
        $this->consumerConfiguration = $consumerConfiguration;
        $this->renderer = $renderer;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        if (!is_dir($this->config['path']) && !mkdir($this->config['path'], 0755, true)) {
            throw new \RuntimeException(
                sprintf(
                    'path "%s" could not be created',
                    $this->config['path']
                )
            );
        }

        $supervisorConfiguration = $this->supervisorConfiguration->generate();

        foreach (['consumers', 'rpc_servers'] as $type) {
            foreach ($this->config[$type] as $name => $consumer) {
                if (!isset($consumer['worker']['queue']['routing'])) {
                    // this can be moved to DI\Configuration
                    $consumer['worker']['queue']['routing'] = [null];
                }

                foreach ($consumer['worker']['queue']['routing'] as $index => $route) {
                    $name = sprintf('%s_%s_%d', substr($type, 0, 1), $name, $index);

                    if ('cli-consumer' === $consumer['processor']) {
                        $consumerConfiguration = sprintf('%s/%s.conf', $this->config['path'], $name);

                        file_put_contents($consumerConfiguration, $this->renderer->render(
                            $this->consumerConfiguration->generate($consumer, $route)->toArray())
                        );

                        $supervisorConfiguration->addSection(
                            $this->supervisorConfiguration->generateProgram(
                                $name,
                                $this->supervisorConfiguration->getConsumerProperties(
                                    $consumer,
                                    $consumerConfiguration
                                )
                            )
                        );
                    } else {
                        $supervisorConfiguration->addSection(
                            $this->supervisorConfiguration->generateProgram(
                                $name,
                                $this->supervisorConfiguration->getBundleProperties($consumer, $route)
                            )
                        );
                    };
                }
            }
        }

        file_put_contents(sprintf('%s/supervisord.conf', $this->config['path']), $this->renderer->render(
            $supervisorConfiguration->toArray()
        ));
    }
}
