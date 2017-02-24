<?php

namespace MyOnlineStore\RabbitMqManagerBundle\Services;

use Symfony\Component\Templating\EngineInterface;

class RabbitMqSupervisor
{
    /**
     * @var Supervisor
     */
    private $supervisor;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $application;

    /**
     * @param Supervisor      $supervisor
     * @param EngineInterface $templating
     * @param array           $config
     * @param string          $application
     */
    public function __construct(Supervisor $supervisor, EngineInterface $templating, array $config, $application)
    {
        $this->supervisor = $supervisor;
        $this->templating = $templating;
        $this->config = $config;
        $this->application = $application;
    }

    /**
     * Generate all supervisor worker configuration files
     */
    public function generate()
    {
        // create directory structure
        foreach (['worker', 'conf.d', 'logs'] as $directory) {
            $path = sprintf('%s/%s/%s', $this->config['path'], $this->application, $directory);

            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }

        // get absolute (root)path
        if (!$path = realpath(sprintf('%s/%s', $this->config['path'], $this->application))) {
            throw new \RuntimeException(sprintf(
                'path "%s/%s" does not exist.',
                $this->config['path'],
                $this->application
            ));
        }

        // create the supervisord.conf configuration file
        file_put_contents(
            sprintf('%s/%s', $path, 'supervisord.conf'),
            $this->templating->render(
                'RabbitMqManagerBundle:Supervisor:supervisor.conf.twig', [
                    'path' => $path,
                ]
            )
        );

        // remove old configuration files
        $this->cleanDir(sprintf('%s/%s', $path, 'conf.d'));
        $this->cleanDir(sprintf('%s/%s', $path, 'worker'));

        foreach (['consumers', 'rpc_servers'] as $type) {
            foreach ($this->config[$type] as $name => $consumer) {
                if (!isset($consumer['worker']['queue']['routing'])) {
                    $this->writeConfig(
                        sprintf('%s_%s', $type, $name),
                        $path,
                        $consumer
                    );

                    continue;
                }

                foreach ($consumer['worker']['queue']['routing'] as $index => $route) {
                    $this->writeConfig(
                        sprintf('%s_%s_%s', $type, $name, $index),
                        $path,
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
     * Stop supervisord and all processes
     */
    public function stop()
    {
        $this->kill('', true);
    }

    /**
     * Start supervisord and all processes
     */
    public function start()
    {
        $this->supervisor->run();
        $this->supervisor->reloadAndUpdate();
    }

    /**
     * Send -HUP to supervisord to gracefully restart all processes
     */
    public function hup()
    {
        $this->kill('HUP');
    }

    /**
     * Send kill signal to supervisord
     *
     * @param string $signal
     * @param bool $waitForProcessToDisappear
     */
    public function kill($signal = '', $waitForProcessToDisappear = false)
    {
        $pid = $this->getSupervisorPid();
        if (!empty($pid) && $this->isProcessRunning($pid)) {
            if (!empty($signal)) {
                $signal = sprintf('-%s', $signal);
            }

            $command = sprintf('kill %s %d', $signal, $pid);

            passthru($command);

            if ($waitForProcessToDisappear) {
                $this->wait();
            }
        }
    }

    /**
     * Wait for supervisord process to disappear
     */
    public function wait()
    {
        $pid = $this->getSupervisorPid();
        if (!empty($pid)) {
            while ($this->isProcessRunning($pid)) {
                sleep(1);
            }
        }
    }

    /**
     * Check if a process with the given pid is running
     *
     * @param int $pid
     * @return bool
     */
    private function isProcessRunning($pid) {
        $state = array();
        exec(sprintf('ps %d', $pid), $state);

        /*
         * ps will return at least one row, the column labels.
         * If the process is running ps will return a second row with its status.
         */
        return 1 < count($state);
    }

    /**
     * Determines the supervisord process id
     *
     * @return null|int
     */
    private function getSupervisorPid() {

        $pidPath = sprintf('%s/%s/%s', realpath($this->config['path']), $this->application, 'supervisord.pid');

        $pid = null;
        if (is_file($pidPath) && is_readable($pidPath)) {
            $pid = (int)file_get_contents($pidPath);
        }

        return $pid;
    }

    /**
     * @param string $path
     */
    private function cleanDir($path)
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
