<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class BaseCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Application
     */
    protected $application;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|InputDefinition
     */
    protected $definition;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|HelperSet
     */
    protected $helperSet;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|KernelInterface
     */
    protected $kernel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ContainerInterface
     */
    protected $container;

    /**
     * @var Command
     */
    protected $command;

    protected function setUp()
    {
        $this->application = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $this->definition = $this->getMockBuilder(InputDefinition::class)->disableOriginalConstructor()->getMock();
        $this->helperSet = $this->getMockBuilder(HelperSet::class)->disableOriginalConstructor()->getMock();

        $this->application->expects($this->any())->method('getDefinition')->will(
            $this->returnValue($this->definition)
        );

        $this->application->expects($this->any())->method('getHelperSet')->will(
            $this->returnValue($this->helperSet)
        );

        $this->application->expects($this->any())->method('getKernel')->willReturn(
            $this->kernel = $this->getMock(KernelInterface::class)
        );

        $this->kernel->expects($this->any())->method('getContainer')->willReturn(
            $this->container = $this->getMock(ContainerInterface::class)
        );
    }
}
