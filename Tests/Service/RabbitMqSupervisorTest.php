<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Tests\Command;

use MyOnlineStore\Bundle\RabbitMqManagerBundle\Services\RabbitMqSupervisor;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Services\Supervisor;
use Symfony\Component\Templating\EngineInterface;

class RabbitMqSupervisorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Supervisor
     */
    private $supervisor;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EngineInterface
     */
    private $templating;

    protected function setUp()
    {
        $this->supervisor = $this->getMockBuilder(Supervisor::class)->disableOriginalConstructor()->getMock();
        $this->templating = $this->getMock(EngineInterface::class);
    }

    public function testExecute()
    {
        $this->supervisor->expects(self::once())->method('run');
        $this->supervisor->expects(self::once())->method('reloadAndUpdate');

        $service = new RabbitMqSupervisor(
            $this->supervisor,
            $this->templating,
            [],
            []
        );

        $service->start();
    }
}
