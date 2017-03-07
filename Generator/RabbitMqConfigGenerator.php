<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Generator;

use Indigo\Ini\Renderer;
use League\Flysystem\FilesystemInterface;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Consumer\ConsumerConfiguration;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Supervisor\SupervisorConfiguration;

final class RabbitMqConfigGenerator implements RabbitMqConfigGeneratorInterface
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
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var array
     */
    private $config;

    /**
     * @param SupervisorConfiguration $supervisorConfiguration
     * @param ConsumerConfiguration   $consumerConfiguration
     * @param Renderer                $renderer
     * @param FilesystemInterface     $filesystem
     * @param array                   $config
     */
    public function __construct(
        SupervisorConfiguration $supervisorConfiguration,
        ConsumerConfiguration $consumerConfiguration,
        Renderer $renderer,
        FilesystemInterface $filesystem,
        array $config
    ) {
        $this->supervisorConfiguration = $supervisorConfiguration;
        $this->consumerConfiguration = $consumerConfiguration;
        $this->renderer = $renderer;
        $this->filesystem = $filesystem;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $supervisorSection = $this->supervisorConfiguration->generate();

        foreach (['consumers', 'rpc_servers'] as $type) {
            foreach ($this->config[$type] as $consumerName => $consumer) {
                if (!isset($consumer['worker']['queue']['routing'])) {
                    // this can be moved to DI\Configuration
                    $consumer['worker']['queue']['routing'] = [null];
                }

                foreach ($consumer['worker']['queue']['routing'] as $index => $route) {
                    $name = sprintf('%s_%s_%d', substr($type, 0, 1), $consumerName, $index);

                    if ('cli-consumer' === $consumer['processor']) {
                        $this->filesystem->put(
                            sprintf('%s.conf', $name),

                            $this->renderer->render(
                                $this->consumerConfiguration->generate($consumer, $route)->toArray()
                            )
                        );

                        $supervisorSection->addSection(
                            $this->supervisorConfiguration->generateProgram(
                                $name,
                                $this->supervisorConfiguration->getConsumerProperties(
                                    $consumer,
                                    sprintf('%s/%s.conf', $this->config['path'], $name)
                                )
                            )
                        );
                    } else {
                        $supervisorSection->addSection(
                            $this->supervisorConfiguration->generateProgram(
                                $name,
                                $this->supervisorConfiguration->getBundleProperties($consumer, $route)
                            )
                        );
                    };
                }
            }
        }

        $this->filesystem->put('supervisord.conf', $this->renderer->render(
            $supervisorSection->toArray()
        ));
    }
}
