<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Generator;

use Symfony\Component\Templating\EngineInterface;

class RabbitMqConfigGenerator implements RabbitMqConfigGeneratorInterface
{
    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var array
     */
    private $config;

    /**
     * @param EngineInterface $templating
     * @param array           $config
     */
    public function __construct(EngineInterface $templating, array $config)
    {
        $this->templating = $templating;
        $this->config = $config;
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

        // create directory structure
        foreach (['worker' => true, 'conf.d' => true, 'logs' => false] as $directory => $cleanup) {
            $path = sprintf('%s/%s', $this->config['path'], $directory);

            if (!is_dir($path)) {
                mkdir($path, 0755);
            }

            if ($cleanup) {
                $this->cleanup($path);
            }
        }

        file_put_contents(
            sprintf('%s/%s', $this->config['path'], 'supervisord.conf'),
            $this->templating->render(
                'RabbitMqManagerBundle:Supervisor:supervisor.conf.twig', [
                    'path' => $this->config['path'],
                ]
            )
        );

        foreach (['consumers', 'rpc_servers'] as $type) {
            foreach ($this->config[$type] as $name => $consumer) {
                if (!isset($consumer['worker']['queue']['routing'])) {
                    $this->writeConfig(
                        sprintf('%s_%s', $type, $name),
                        $this->config['path'],
                        $consumer
                    );

                    continue;
                }

                foreach ($consumer['worker']['queue']['routing'] as $index => $route) {
                    $this->writeConfig(
                        sprintf('%s_%s_%s', $type, $name, $index),
                        $this->config['path'],
                        $consumer,
                        $route
                    );
                }
            }
        }
    }

    /**
     * @param string $name
     * @param string $path
     * @param array  $consumer
     * @param null   $route
     */
    private function writeConfig($name, $path, array $consumer, $route = null)
    {
        if ('cli-consumer' === $consumer['processor']) {
            // write additional cli-consumer config
            file_put_contents(
                $consumerConfiguration = sprintf('%s/worker/%s.conf', $path, $name),
                $this->templating->render('RabbitMqManagerBundle:Supervisor:consumer.conf.twig', [
                    'path' => $path,
                    'routing_key' => $route,
                    'consumer' => $consumer,
                ])
            );

            $content = $this->templating->render('RabbitMqManagerBundle:Supervisor/processor:cli-consumer.conf.twig', [
                'path' => $path,
                'configuration' => $consumerConfiguration,
                'consumer' => $consumer,
            ]);
        } else {
            $consumer['command']['arguments'][] = sprintf('--messages=%s', $consumer['messages']);

            if (null !== $route) {
                $consumer['command']['arguments'][] = sprintf('--route=%s', $route);
            }

            $content = $this->templating->render('RabbitMqManagerBundle:Supervisor/processor:bundle.conf.twig', [
                'path' => $path,
                'consumer' => $consumer,
            ]);
        }

        file_put_contents(
            sprintf('%s/conf.d/%s.conf', $path, $name),
            $content
        );
    }

    /**
     * @param string $path
     */
    private function cleanup($path)
    {
        /** @var \SplFileInfo $item */
        foreach (new \DirectoryIterator($path) as $item) {
            if ($item->isDir()) {
                continue;
            }

            if ('conf' !== $item->getExtension()) {
                continue;
            }

            unlink($item->getRealPath());
        }
    }
}
