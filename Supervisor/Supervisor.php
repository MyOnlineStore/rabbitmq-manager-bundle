<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Supervisor;

use MyOnlineStore\Bundle\RabbitMqManagerBundle\Exception\Supervisor\SupervisorAlreadyRunningException;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Process\ProcessBuilderFactoryInterface;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Process\ProcessInterface;

final class Supervisor implements SupervisorInterface
{
    /**
     * @var ProcessBuilderFactoryInterface
     */
    private $processBuilderFactory;

    /**
     * @var string
     */
    private $path;

    /**
     * @param ProcessBuilderFactoryInterface $processBuilderFactory
     * @param array                          $config
     */
    public function __construct(ProcessBuilderFactoryInterface $processBuilderFactory, array $config)
    {
        $this->processBuilderFactory = $processBuilderFactory;
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

        $processBuilder = $this->processBuilderFactory->create();
        $processBuilder->setWorkingDirectory($this->path);
        $processBuilder->setPrefix('supervisord');
        $processBuilder->add(sprintf('--configuration=%s/%s', $this->path, 'supervisord.conf'));
        $processBuilder->add(sprintf('--identifier=%s', sha1($this->path)));
        $processBuilder->add(' &');
        $processBuilder->disableOutput();

        $processBuilder->getProcess()->run();
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
     * @return ProcessInterface
     */
    private function execute($cmd)
    {
        $processBuilder = $this->processBuilderFactory->create();
        $processBuilder->setWorkingDirectory($this->path);
        $processBuilder->setPrefix('supervisorctl');
        $processBuilder->add(sprintf('--configuration=%s/%s', $this->path, 'supervisord.conf'));
        $processBuilder->add($cmd);

        $process = $processBuilder->getProcess();

        $process->run();
        $process->wait();

        return $process;
    }
}
