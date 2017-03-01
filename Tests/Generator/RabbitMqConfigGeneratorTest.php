<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Tests\Generator;

use Indigo\Ini\Renderer;
use League\Flysystem\FilesystemInterface;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Consumer\ConsumerConfiguration;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Section\SectionCollection;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Supervisor\SupervisorConfiguration;
use MyOnlineStore\Bundle\RabbitMqManagerBundle\Generator\RabbitMqConfigGenerator;
use Supervisor\Configuration\Section;

class RabbitMqConfigGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SupervisorConfiguration
     */
    private $supervisorConfiguration;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ConsumerConfiguration
     */
    private $consumerConfiguration;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Renderer
     */
    private $renderer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FilesystemInterface
     */
    private $filesystem;

    protected function setUp()
    {
        $this->supervisorConfiguration = $this->getMockBuilder(SupervisorConfiguration::class)->disableOriginalConstructor()->getMock();
        $this->consumerConfiguration = $this->getMockBuilder(ConsumerConfiguration::class)->disableOriginalConstructor()->getMock();
        $this->renderer = $this->getMockBuilder(Renderer::class)->disableOriginalConstructor()->getMock();
        $this->filesystem = $this->getMock(FilesystemInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function testGenerateEmptyConfig()
    {
        $config = [
            'path' => '/my/path',
            'consumers' => [],
            'rpc_servers' => [],
        ];

        $this->supervisorConfiguration->expects($this->once())->method('generate')->willReturn(
            $supervisorSectionCollection = new SectionCollection()
        );

        $this->renderer->expects($this->once())->method('render')->with([])->willReturn('foobar');

        $this->filesystem->expects($this->once())->method('has')->with('supervisord.conf')->willReturn(true);
        $this->filesystem->expects($this->once())->method('update')->with('supervisord.conf', 'foobar');

        $generator = new RabbitMqConfigGenerator(
            $this->supervisorConfiguration,
            $this->consumerConfiguration,
            $this->renderer,
            $this->filesystem,
            $config
        );

        $generator->generate();
    }

    /**
     * @inheritdoc
     */
    public function testGenerateCliConsumers()
    {
        $config = [
            'path' => '/my/path',
            'consumers' => [
                'first_consumer' => [
                    'processor' => 'cli-consumer',
                    'worker' => [
                        'queue' => [
                            'routing' => [
                                'first-routing',
                                'second-routing',
                            ],
                        ],
                    ],
                ],
            ],
            'rpc_servers' => [],
        ];

        $this->supervisorConfiguration->expects($this->once())->method('generate')->willReturn(
            new SectionCollection()
        );

        $this->supervisorConfiguration->expects($this->exactly(2))->method('generateProgram')->withConsecutive(
            ['c_first_consumer_0'],
            ['c_first_consumer_1']
        )->willReturn(
            $this->getMock(Section::class)
        );

        $this->supervisorConfiguration->expects($this->exactly(2))->method('getConsumerProperties')->withConsecutive(
            [[
                'processor' => 'cli-consumer',
                'worker' => [
                    'queue' => [
                        'routing' => [
                            'first-routing',
                            'second-routing',
                        ],
                    ],
                ],
            ], '/my/path/c_first_consumer_0.conf'],
            [[
                'processor' => 'cli-consumer',
                'worker' => [
                    'queue' => [
                        'routing' => [
                            'first-routing',
                            'second-routing',
                        ],
                    ],
                ],
            ], '/my/path/c_first_consumer_1.conf']
        )->willReturn([]);

        $this->consumerConfiguration->expects($this->exactly(2))->method('generate')->withConsecutive(
            [[
                'processor' => 'cli-consumer',
                'worker' => [
                    'queue' => [
                        'routing' => [
                            'first-routing',
                            'second-routing',
                        ],
                    ],
                ],
            ], 'first-routing'],
            [[
                'processor' => 'cli-consumer',
                'worker' => [
                    'queue' => [
                        'routing' => [
                            'first-routing',
                            'second-routing',
                        ],
                    ],
                ],
            ], 'second-routing']
        )->willReturn(
            new SectionCollection()
        );

        $this->renderer->expects($this->exactly(3))->method('render');

        $this->filesystem->expects($this->exactly(3))->method('has')->withConsecutive(
            ['c_first_consumer_0.conf'],
            ['c_first_consumer_1.conf'],
            ['supervisord.conf']
        )->willReturnOnConsecutiveCalls(
            true,
            false,
            true
        );

        $this->filesystem->expects($this->exactly(2))->method('update')->withConsecutive(
            ['c_first_consumer_0.conf'],
            ['supervisord.conf']
        );
        $this->filesystem->expects($this->exactly(1))->method('write')->with('c_first_consumer_1.conf');

        $generator = new RabbitMqConfigGenerator(
            $this->supervisorConfiguration,
            $this->consumerConfiguration,
            $this->renderer,
            $this->filesystem,
            $config
        );

        $generator->generate();
    }

    /**
     * @inheritdoc
     */
    public function testGenerateBundleConsumer()
    {
        $config = [
            'path' => '/my/path',
            'consumers' => [
                'first_consumer' => [
                    'processor' => 'bundle',
                    'worker' => [
                        'queue' => [
                            'routing' => [
                                'first-routing',
                                'second-routing',
                            ],
                        ],
                    ],
                ],
            ],
            'rpc_servers' => [],
        ];

        $this->supervisorConfiguration->expects($this->once())->method('generate')->willReturn(
            new SectionCollection()
        );

        $this->supervisorConfiguration->expects($this->exactly(2))->method('generateProgram')->withConsecutive(
            ['c_first_consumer_0'],
            ['c_first_consumer_1']
        )->willReturn(
            $this->getMock(Section::class)
        );

        $this->supervisorConfiguration->expects($this->exactly(2))->method('getBundleProperties')->withConsecutive(
            [[
                'processor' => 'bundle',
                'worker' => [
                    'queue' => [
                        'routing' => [
                            'first-routing',
                            'second-routing',
                        ],
                    ],
                ],
            ], 'first-routing'],
            [[
                'processor' => 'bundle',
                'worker' => [
                    'queue' => [
                        'routing' => [
                            'first-routing',
                            'second-routing',
                        ],
                    ],
                ],
            ], 'second-routing']
        )->willReturn([]);

        $this->renderer->expects($this->once())->method('render');

        $this->filesystem->expects($this->once())->method('has')->with('supervisord.conf')->willReturn(false);

        $this->filesystem->expects($this->once())->method('write')->with('supervisord.conf');

        $generator = new RabbitMqConfigGenerator(
            $this->supervisorConfiguration,
            $this->consumerConfiguration,
            $this->renderer,
            $this->filesystem,
            $config
        );

        $generator->generate();
    }

    /**
     * @inheritdoc
     */
    public function testGenerateRpcServer()
    {
        $config = [
            'path' => '/my/path',
            'consumers' => [],
            'rpc_servers' => [
                'first_consumer' => [
                    'processor' => 'bundle',
                    'worker' => [
                        'queue' => [
                            'routing' => [
                                'first-routing',
                                'second-routing',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->supervisorConfiguration->expects($this->once())->method('generate')->willReturn(
            new SectionCollection()
        );

        $this->supervisorConfiguration->expects($this->exactly(2))->method('generateProgram')->withConsecutive(
            ['r_first_consumer_0'],
            ['r_first_consumer_1']
        )->willReturn(
            $this->getMock(Section::class)
        );

        $this->supervisorConfiguration->expects($this->exactly(2))->method('getBundleProperties')->withConsecutive(
            [[
                'processor' => 'bundle',
                'worker' => [
                    'queue' => [
                        'routing' => [
                            'first-routing',
                            'second-routing',
                        ],
                    ],
                ],
            ], 'first-routing'],
            [[
                'processor' => 'bundle',
                'worker' => [
                    'queue' => [
                        'routing' => [
                            'first-routing',
                            'second-routing',
                        ],
                    ],
                ],
            ], 'second-routing']
        )->willReturn([]);

        $this->renderer->expects($this->once())->method('render');

        $this->filesystem->expects($this->once())->method('has')->with('supervisord.conf')->willReturn(false);

        $this->filesystem->expects($this->once())->method('write')->with('supervisord.conf');

        $generator = new RabbitMqConfigGenerator(
            $this->supervisorConfiguration,
            $this->consumerConfiguration,
            $this->renderer,
            $this->filesystem,
            $config
        );

        $generator->generate();
    }
}
