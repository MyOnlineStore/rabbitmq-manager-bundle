<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Consumer;

use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Section\SectionCollection;

final class ConsumerConfiguration
{
    /**
     * @var ConsumerSectionFactoryInterface
     */
    private $sectionFactory;

    /**
     * @var string
     */
    private $path;


    /**
     * @param ConsumerSectionFactoryInterface $sectionFactory
     * @param string                          $path
     */
    public function __construct(ConsumerSectionFactoryInterface $sectionFactory, $path)
    {
        $this->sectionFactory = $sectionFactory;
        $this->path = $path;
    }

    /**
     * @param array       $consumer
     * @param null|string $route
     *
     * @return SectionCollection
     */
    public function generate(array $consumer, $route = null)
    {
        $sections = new SectionCollection();

        $sections->addSection(
            $this->sectionFactory->createRabbitmq(
                [
                    'host' => $consumer['connection']['host'],
                    'username' => $consumer['connection']['user'],
                    'password' => $consumer['connection']['password'],
                    'vhost' => $consumer['connection']['vhost'],
                    'port' => $consumer['connection']['port'],
                    'queue' => $consumer['worker']['queue']['name'],
                    'compression' => $consumer['worker']['compression'],
                ]
            )
        );

        if ($consumer['worker']['prefetch']['count'] > 0) {
            $sections->addSection(
                $this->sectionFactory->createPrefetch(
                    [
                        'count' => $consumer['worker']['prefetch']['count'],
                        'global' => $consumer['worker']['prefetch']['global'],
                    ]
                )
            );
        }

        if (isset($consumer['worker']['exchange'])) {
            $sections->addSection(
                $this->sectionFactory->createExchange(
                    [
                        'name' => $consumer['worker']['exchange']['name'],
                        'type' => $consumer['worker']['exchange']['type'],
                        'durable' => $consumer['worker']['exchange']['durable'],
                        'autodelete' => $consumer['worker']['exchange']['autodelete'],
                    ]
                )
            );
        }

        if (null !== $route) {
            $sections->addSection(
                $this->sectionFactory->createQueue(
                    [
                        'routingkey' => $route,
                    ]
                )
            );
        }

        $sections->addSection(
            $this->sectionFactory->createLogs(
                [
                    'info' => sprintf('%s/consumer.log', $this->path),
                    'error' => sprintf('%s/consumer.err', $this->path),
                ]
            )
        );

        return $sections;
    }
}
