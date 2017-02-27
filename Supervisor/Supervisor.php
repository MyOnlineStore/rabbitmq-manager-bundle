<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Supervisor;

use MyOnlineStore\Bundle\RabbitMqManagerBundle\Exception\Supervisor\SupervisorAlreadyRunningException;
use Symfony\Component\Process\Process;

class Supervisor implements SupervisorInterface
{
    /**
     * @var string
     */
    private $path;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->path = $config['path'];
    }

    /**
     * @inheritdoc
     */
    public function isRunning()
    {
        $result = $this->execute('status')->getOutput();

        return !(false !== strpos($result, 'sock no such file') || false !== strpos($result, 'refused connection'));
    }

    /**
     * @inheritdoc
     */
    public function start()
    {
        if ($this->isRunning()) {
            throw new SupervisorAlreadyRunningException('supervisor is already running.');
        }

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

    /**
     * @inheritdoc
     */
    public function stop()
    {
        if (!$this->isRunning()) {
            return;
        }

        $this->execute('shutdown');
    }

    /**
     * @inheritdoc
     */
    public function reload()
    {
        if (!$this->isRunning()) {
            return;
        }

        $this->execute('reread');
        $this->execute('reload');
    }

    /**
     * @inheritdoc
     */
    public function restart()
    {
        $this->execute('restart');
    }

    /**
     * @inheritdoc
     */
    public function getProcessId() {

        return (int) $this->execute('pid')->getOutput();
    }

    /**
     * @param string $cmd supervisorctl command
     *
     * @return Process
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
}
