<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Tests\Supervisor;

use MyOnlineStore\Bundle\RabbitMqManagerBundle\Supervisor\Supervisor;
use Symfony\Component\Process\Process;

class SupervisorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Supervisor
     */
    private $supervisor;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Process
     */
    private $process;

    protected function setUp()
    {
        $this->supervisor = $this->getMock(Supervisor::class, ['start', 'execute'], [['path' => '/path']]);
        $this->process = $this->getMockBuilder(Process::class)->disableOriginalConstructor()->getMock();
    }

    public function testIsRunning()
    {
        $this->process->expects($this->once())->method('getOutput')->willReturn('');

        $this->supervisor->expects($this->once())->method('execute')->with('status')->willReturn(
            $this->process
        );

        $this->assertTrue($this->supervisor->isRunning());
    }

    public function testIsNotRunning()
    {
        $this->process->expects($this->once())->method('getOutput')->willReturn('sock no such file');

        $this->supervisor->expects($this->once())->method('execute')->with('status')->willReturn(
            $this->process
        );

        $this->assertFalse($this->supervisor->isRunning());
    }

    public function testStopWithRunningState()
    {
        $this->process->expects($this->once())->method('getOutput')->willReturn('');

        $this->supervisor->expects($this->exactly(2))->method('execute')->withConsecutive(['status'], ['shutdown'])->willReturn(
            $this->process
        );

        $this->supervisor->stop();
    }

    public function testStopWithStoppedState()
    {
        $this->process->expects($this->once())->method('getOutput')->willReturn('sock no such file');

        $this->supervisor->expects($this->once())->method('execute')->with('status')->willReturn(
            $this->process
        );

        $this->supervisor->stop();
    }

    public function testReloadWithRunningState()
    {
        $this->process->expects($this->once())->method('getOutput')->willReturn('');

        $this->supervisor->expects($this->exactly(3))->method('execute')->withConsecutive(['status'], ['reread'], ['reload'])->willReturn(
            $this->process
        );

        $this->supervisor->reload();
    }

    public function testReloadWithStoppedState()
    {
        $this->process->expects($this->once())->method('getOutput')->willReturn('sock no such file');

        $this->supervisor->expects($this->once())->method('execute')->with('status')->willReturn(
            $this->process
        );

        $this->supervisor->reload();
    }

    public function testRestart()
    {
        $this->supervisor->expects($this->once())->method('execute')->with('restart');

        $this->supervisor->restart();
    }

    public function testGetProcessId()
    {
        $this->process->expects($this->once())->method('getOutput')->willReturn('4623');

        $this->supervisor->expects($this->once())->method('execute')->with('pid')->willReturn(
            $this->process
        );

        $processId = $this->supervisor->getProcessId();
        $this->assertEquals(4623, $processId);
        $this->assertInternalType('integer', $processId);
    }
}
