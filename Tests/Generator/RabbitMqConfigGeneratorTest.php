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
        $this->supervisorConfiguration = $this->getMockBuilder(SupervisorConfiguration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->consumerConfiguration = $this->getMockBuilder(ConsumerConfiguration::class)
            ->disableOriginalConstructor()
            ->getMock();
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
        $this->filesystem->expects($this->once())->method('put')->with('supervisord.conf', 'foobar');

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

        $section = $this->getMock(Section::class);
        $section->method('getName')->willReturn('supervisor');
        $section->method('getProperties')->willReturn('supervisor-properties');

        $this->supervisorConfiguration->expects($this->exactly(2))->method('generateProgram')->withConsecutive(
            ['c_first_consumer_0'],
            ['c_first_consumer_1']
        )->willReturn($section);

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

        $this->renderer->expects($this->exactly(3))->method('render')->withConsecutive(
            [[]],
            [[]],
            [['supervisor' => 'supervisor-properties']]
        )->willReturn('rendered-content');

        $this->filesystem->expects($this->exactly(3))->method('put')->withConsecutive(
            ['c_first_consumer_0.conf', 'rendered-content'],
            ['c_first_consumer_1.conf', 'rendered-content'],
            ['supervisord.conf', 'rendered-content']
        );

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

        $section = $this->getMock(Section::class);
        $section->method('getName')->willReturn('supervisor');
        $section->method('getProperties')->willReturn('supervisor-properties');

        $this->supervisorConfiguration->expects($this->exactly(2))->method('generateProgram')->withConsecutive(
            ['c_first_consumer_0'],
            ['c_first_consumer_1']
        )->willReturn($section);

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

        $this->renderer->expects($this->once())->method('render')
            ->with(['supervisor' => 'supervisor-properties'])
            ->willReturn('rendered-content');
        $this->filesystem->expects($this->once())->method('put')->with('supervisord.conf', 'rendered-content');

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

        $section = $this->getMock(Section::class);
        $section->method('getName')->willReturn('supervisor');
        $section->method('getProperties')->willReturn('supervisor-properties');

        $this->supervisorConfiguration->expects($this->exactly(2))->method('generateProgram')->withConsecutive(
            ['r_first_consumer_0'],
            ['r_first_consumer_1']
        )->willReturn($section);

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

        $this->renderer->expects($this->once())->method('render')
            ->with(['supervisor' => 'supervisor-properties'])
            ->willReturn('rendered-content');

        $this->filesystem->expects($this->once())->method('put')->with('supervisord.conf', 'rendered-content');

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
