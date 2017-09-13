<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Tests\Supervisor;

use MyOnlineStore\Bundle\RabbitMqManagerBundle\Exception\Supervisor\SupervisorAlreadyRunningException;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Process\ProcessBuilderFactoryInterface;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Process\ProcessBuilderInterface;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Process\ProcessInterface;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Supervisor\Supervisor;

class SupervisorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ProcessBuilderFactoryInterface
     */
    private $factory;

    /**
     * @var Supervisor
     */
    private $supervisor;

    protected function setUp()
    {
        $this->factory = $this->getMock(ProcessBuilderFactoryInterface::class);

        $this->supervisor = new Supervisor(
            $this->factory,
            [
                'path' => '/path/to/supervisor',
            ]
        );
    }

    public function testIsRunning()
    {
        $this->factory->expects($this->once())->method('create')->willReturn(
            $this->isRunning(true)
        );

        $this->assertTrue($this->supervisor->isRunning());
    }

    public function testIsNotRunning()
    {
        $this->factory->expects($this->once())->method('create')->willReturn(
            $this->isRunning(false)
        );

        $this->assertFalse($this->supervisor->isRunning());
    }

    public function testStart()
    {
        $this->factory->expects($this->exactly(2))->method('create')->willReturnOnConsecutiveCalls(
            $this->isRunning(false),
            $processBuilder = $this->getMock(ProcessBuilderInterface::class)
        );

        $processBuilder->expects(self::once())->method('setWorkingDirectory')->with('/path/to/supervisor');
        $processBuilder->expects($this->once())->method('setPrefix')->with('supervisord');
        $processBuilder->expects($this->exactly(3))->method('add')->withConsecutive(
            ['--configuration=/path/to/supervisor/supervisord.conf'],
            ['--identifier=49e4b94fd261dc17cfecef2a6d3ea83b91d69f14'],
            [' &']
        );
        $processBuilder->expects($this->once())->method('disableOutput');

        $processBuilder->expects($this->once())->method('getProcess')->willReturn(
            $process = $this->getMock(ProcessInterface::class)
        );

        $process->expects($this->once())->method('run');

        $this->supervisor->start();
    }

    public function testStartAlreadyRunning()
    {
        $this->setExpectedException(SupervisorAlreadyRunningException::class);

        $this->factory->expects($this->once())->method('create')->willReturn(
            $this->isRunning(true)
        );

        $this->supervisor->start();
    }

    public function testStopWithRunningState()
    {
        $this->factory->expects($this->exactly(2))->method('create')->willReturnOnConsecutiveCalls(
            $this->isRunning(true),
            $processBuilder = $this->getMock(ProcessBuilderInterface::class)
        );

        $processBuilder->expects(self::once())->method('setWorkingDirectory')->with('/path/to/supervisor');
        $processBuilder->expects($this->once())->method('setPrefix')->with('supervisorctl');
        $processBuilder->expects($this->exactly(2))->method('add')->withConsecutive(
            ['--configuration=/path/to/supervisor/supervisord.conf'],
            ['shutdown']
        );

        $processBuilder->expects($this->once())->method('getProcess')->willReturn(
            $process = $this->getMock(ProcessInterface::class)
        );

        $process->expects($this->once())->method('run');
        $this->supervisor->stop();
    }

    public function testStopWithStoppedState()
    {
        $this->factory->expects($this->once())->method('create')->willReturn(
            $this->isRunning(false)
        );

        $this->supervisor->stop();
    }

    public function testReloadWithRunningState()
    {
        $this->factory->expects($this->exactly(3))->method('create')->willReturnOnConsecutiveCalls(
            $this->isRunning(true),
            $processBuilder = $this->getMock(ProcessBuilderInterface::class),
            $processBuilder
        );

        $processBuilder->expects($this->exactly(2))->method('setWorkingDirectory')->with('/path/to/supervisor');
        $processBuilder->expects($this->exactly(2))->method('setPrefix')->with('supervisorctl');
        $processBuilder->expects($this->exactly(4))->method('add')->withConsecutive(
            ['--configuration=/path/to/supervisor/supervisord.conf'],
            ['reread'],
            ['--configuration=/path/to/supervisor/supervisord.conf'],
            ['reload']
        );

        $processBuilder->expects($this->exactly(2))->method('getProcess')->willReturn(
            $process = $this->getMock(ProcessInterface::class)
        );

        $process->expects($this->exactly(2))->method('run');

        $this->supervisor->reload();
    }

    public function testReloadWithStoppedState()
    {
        $this->factory->expects($this->once())->method('create')->willReturn(
            $this->isRunning(false)
        );

        $this->supervisor->reload();
    }

    public function testRestart()
    {
        $this->factory->expects($this->once())->method('create')->willReturnOnConsecutiveCalls(
            $processBuilder = $this->getMock(ProcessBuilderInterface::class),
            $processBuilder
        );

        $processBuilder->expects($this->once())->method('setWorkingDirectory')->with('/path/to/supervisor');
        $processBuilder->expects($this->once())->method('setPrefix')->with('supervisorctl');
        $processBuilder->expects($this->exactly(2))->method('add')->withConsecutive(
            ['--configuration=/path/to/supervisor/supervisord.conf'],
            ['restart']
        );

        $processBuilder->expects($this->once())->method('getProcess')->willReturn(
            $process = $this->getMock(ProcessInterface::class)
        );

        $process->expects($this->once())->method('run');

        $this->supervisor->restart();
    }

    public function testGetProcessId()
    {
        $this->factory->expects($this->once())->method('create')->willReturn(
            $processBuilder = $this->getMock(ProcessBuilderInterface::class)
        );

        $processBuilder->expects($this->once())->method('setWorkingDirectory')->with('/path/to/supervisor');
        $processBuilder->expects($this->once())->method('setPrefix')->with('supervisorctl');
        $processBuilder->expects($this->exactly(2))->method('add')->withConsecutive(
            ['--configuration=/path/to/supervisor/supervisord.conf'],
            ['pid']
        );

        $processBuilder->expects($this->once())->method('getProcess')->willReturn(
            $process = $this->getMock(ProcessInterface::class)
        );

        $process->expects($this->once())->method('run');
        $process->expects($this->once())->method('wait');

        $process->expects($this->once())->method('getOutput')->willReturn('4623');

        $processId = $this->supervisor->getProcessId();
        $this->assertEquals(4623, $processId);
        $this->assertInternalType('integer', $processId);
    }

    /**
     * @param bool $status
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function isRunning($status = true)
    {
        $processBuilder = $this->getMock(ProcessBuilderInterface::class);

        $processBuilder->expects($this->once())->method('setWorkingDirectory')->with('/path/to/supervisor');
        $processBuilder->expects($this->once())->method('setPrefix')->with('supervisorctl');
        $processBuilder->expects($this->exactly(2))->method('add')->withConsecutive(
            ['--configuration=/path/to/supervisor/supervisord.conf'],
            ['status']
        );

        $processBuilder->expects($this->once())->method('getProcess')->willReturn(
            $process = $this->getMock(ProcessInterface::class)
        );

        $process->expects($this->once())->method('run');
        $process->expects($this->once())->method('wait');

        $process->expects($this->once())->method('getOutput')->willReturn($status ? '' : 'sock no such file');

        return $processBuilder;
    }
}
