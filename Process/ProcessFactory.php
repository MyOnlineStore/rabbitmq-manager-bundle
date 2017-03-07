<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Process;

use Symfony\Component\Process\Process as SymfonyProcess;

final class ProcessFactory implements ProcessFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function create($commandLine, $cwd = null, array $env = null, $input = null, $timeout = 60, array $options = [])
    {
        return new Process(
            $commandLine,
            $cwd,
            $env,
            $input,
            $timeout,
            $options
        );
    }

    /**
     * @inheritdoc
     */
    public function createFromProcess(SymfonyProcess $process)
    {
        return new Process(
            $process->getCommandLine(),
            $process->getWorkingDirectory(),
            $process->getEnv(),
            $process->getInput(),
            $process->getTimeout(),
            $process->getOptions()
        );
    }
}
