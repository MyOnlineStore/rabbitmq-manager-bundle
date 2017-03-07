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
        $supervisorConfiguration = $this->supervisorConfiguration->generate();

        foreach (['consumers', 'rpc_servers'] as $type) {
            foreach ($this->config[$type] as $consumerName => $consumer) {
                if (!isset($consumer['worker']['queue']['routing'])) {
                    // this can be moved to DI\Configuration
                    $consumer['worker']['queue']['routing'] = [null];
                }

                foreach ($consumer['worker']['queue']['routing'] as $index => $route) {
                    $name = sprintf('%s_%s_%d', substr($type, 0, 1), $consumerName, $index);

                    if ('cli-consumer' === $consumer['processor']) {
                        $this->write(
                            sprintf('%s.conf', $name),
                            $this->renderer->render($this->consumerConfiguration->generate($consumer, $route)->toArray())
                        );

                        $supervisorConfiguration->addSection(
                            $this->supervisorConfiguration->generateProgram(
                                $name,
                                $this->supervisorConfiguration->getConsumerProperties(
                                    $consumer,
                                    sprintf('%s/%s.conf', $this->config['path'], $name)
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

        $this->write('supervisord.conf', $this->renderer->render(
            $supervisorConfiguration->toArray()
        ));
    }

    /**
     * @param string $path
     * @param string $content
     */
    private function write($path, $content)
    {
        if ($this->filesystem->has($path)) {
            $this->filesystem->update($path, $content);
        } else {
            $this->filesystem->write($path, $content);
        }
    }
}
