<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Supervisor;

use Supervisor\Configuration\Section\Program;
use Supervisor\Configuration\Section\RpcInterface;
use Supervisor\Configuration\Section\Supervisorctl;
use Supervisor\Configuration\Section\Supervisord;
use Supervisor\Configuration\Section\UnixHttpServer;

final class SupervisorSectionFactory implements SupervisorSectionFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function createSupervisord(array $properties = [])
    {
        return new Supervisord($properties);
    }

    /**
     * @inheritdoc
     */
    public function createRpcInterface($name, array $properties = [])
    {
        return new RpcInterface($name, $properties);
    }

    /**
     * @inheritdoc
     */
    public function createSupervisorctl(array $properties = [])
    {
        return new Supervisorctl($properties);
    }

    /**
     * @inheritdoc
     */
    public function createProgram($name, array $properties = [])
    {
        return new Program($name, $properties);
    }

    /**
     * @inheritdoc
     */
    public function createUnixHttpServer(array $properties = [])
    {
        return new UnixHttpServer($properties);
    }
}
