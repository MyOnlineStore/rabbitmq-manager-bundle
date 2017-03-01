<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Tests\Configuration\Supervisor;

use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Supervisor\SupervisorSectionFactory;
use Supervisor\Configuration\Section\Program;
use Supervisor\Configuration\Section\RpcInterface;
use Supervisor\Configuration\Section\Supervisorctl;
use Supervisor\Configuration\Section\Supervisord;
use Supervisor\Configuration\Section\UnixHttpServer;

class SupervisorSectionFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SupervisorSectionFactory
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = new SupervisorSectionFactory();
    }

    public function testCreateProgram()
    {
        $this->assertInstanceOf(Program::class, $this->factory->createProgram('name', [
            'command' => 'foo',
        ]));
    }

    public function testCreateRpcInterface()
    {
        $this->assertInstanceOf(RpcInterface::class, $this->factory->createRpcInterface('name', []));
    }

    public function testCreateSupervisord()
    {
        $this->assertInstanceOf(Supervisord::class, $this->factory->createSupervisord([]));
    }

    /**
     * @inheritdoc
     */
    public function testCreateSupervisorctl()
    {
        $this->assertInstanceOf(Supervisorctl::class, $this->factory->createSupervisorctl([]));
    }

    /**
     * @inheritdoc
     */
    public function testCreateUnixHttpServer()
    {
        $this->assertInstanceOf(UnixHttpServer::class, $this->factory->createUnixHttpServer([]));
    }
}
