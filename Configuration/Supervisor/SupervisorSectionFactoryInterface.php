<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Supervisor;

use Supervisor\Configuration\Section\Program;
use Supervisor\Configuration\Section\RpcInterface;
use Supervisor\Configuration\Section\Supervisorctl;
use Supervisor\Configuration\Section\Supervisord;
use Supervisor\Configuration\Section\UnixHttpServer;

interface SupervisorSectionFactoryInterface
{
    /**
     * @param array $properties
     *
     * @return Supervisord
     */
    public function createSupervisord(array $properties = []);

    /**
     * @param string $name
     * @param array  $properties
     *
     * @return RpcInterface
     */
    public function createRpcInterface($name, array $properties = []);

    /**
     * @param array $properties
     *
     * @return Supervisorctl
     */
    public function createSupervisorctl(array $properties = []);

    /**
     * @param string $name
     * @param array  $properties
     *
     * @return Program
     */
    public function createProgram($name, array $properties = []);

    /**
     * @param array $properties
     *
     * @return UnixHttpServer
     */
    public function createUnixHttpServer(array $properties = []);
}
