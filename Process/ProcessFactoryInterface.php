<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Process;

use Symfony\Component\Process\Process;

interface ProcessFactoryInterface
{
    /**
     * @param string     $commandLine
     * @param null       $cwd
     * @param array|null $env
     * @param null       $input
     * @param int        $timeout
     * @param array      $options
     *
     * @return ProcessInterface
     */
    public function create(
        $commandLine,
        $cwd = null,
        array $env = null,
        $input = null,
        $timeout = 60,
        array $options = []
    );

    /**
     * @param Process $process
     *
     * @return ProcessInterface
     */
    public function createFromProcess(Process $process);
}
