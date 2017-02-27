<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Manager;

interface RabbitMqManagerInterface
{
    /**
     * Generate all supervisor worker configuration files
     */
    public function generate();

    /**
     * Stop supervisord and all processes
     */
    public function stop();

    /**
     * Start supervisord and all processes
     */
    public function start();

    /**
     * Send -HUP to supervisord to gracefully restart all processes
     */
    public function hup();

    /**
     * Send kill signal to supervisord
     *
     * @param string $signal
     * @param bool   $waitForProcessToDisappear
     */
    public function kill($signal = '', $waitForProcessToDisappear = false);

    /**
     * Wait for supervisord process to disappear
     */
    public function wait();
}
