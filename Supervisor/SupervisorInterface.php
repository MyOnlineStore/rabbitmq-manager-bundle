<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Supervisor;

use MyOnlineStore\Bundle\RabbitMqManagerBundle\Exception\Supervisor\SupervisorAlreadyRunningException;

interface SupervisorInterface
{
    /**
     * @return bool
     */
    public function isRunning();

    /**
     * @throws SupervisorAlreadyRunningException
     */
    public function start();

    public function stop();

    public function reload();

    public function restart();

    /**
     * @return int
     */
    public function getProcessId();
}
