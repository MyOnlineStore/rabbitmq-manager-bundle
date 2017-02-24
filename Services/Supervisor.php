<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Services;

use Symfony\Component\Process\Process;

class Supervisor
{
    /**
     * @var string
     */
    private $path;

    /**
     * @param array $config
     * @param       $application
     */
    public function __construct(array $config, $application)
    {
        $this->path = realpath(sprintf('%s/%s', $config['path'], $application));
    }

    /**
     * Execute a supervisorctl command
     *
     * @param $cmd string supervisorctl command
     * @return \Symfony\Component\Process\Process
     */
    public function execute($cmd)
    {
        $process = new Process(
            sprintf(
                'supervisorctl%1$s %2$s',
                sprintf(' --configuration=%s/%s', $this->path, 'supervisord.conf'),
                $cmd
            )
        );
        $process->setWorkingDirectory($this->path);
        $process->run();
        $process->wait();

        return $process;
    }

    /**
     * Update configuration and processes
     */
    public function reloadAndUpdate()
    {
        $this->execute('reread');
        $this->execute('update');
    }

    /**
     * Start supervisord if not already running
     */
    public function run()
    {
        $result = $this->execute('status')->getOutput();

        if (strpos($result, 'sock no such file') || strpos($result, 'refused connection')) {
            $process = new Process(
                sprintf(
                    'supervisord%1$s%2$s',
                    sprintf(' --configuration=%s/%s', $this->path, 'supervisord.conf'),
                    sprintf(' --identifier=%s', sha1($this->path))
                )
            );

            $process->setWorkingDirectory($this->path);
            $process->run();
        }
    }
}
