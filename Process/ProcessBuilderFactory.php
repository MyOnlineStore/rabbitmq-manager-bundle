<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Process;

class ProcessBuilderFactory implements ProcessBuilderFactoryInterface
{
    /**
     * @var ProcessFactoryInterface
     */
    private $processFactory;

    /**
     * @param ProcessFactoryInterface $processFactory
     */
    public function __construct(ProcessFactoryInterface $processFactory)
    {
        $this->processFactory = $processFactory;
    }

    /**
     * @inheritdoc
     */
    public function create(array $arguments = [])
    {
        return new ProcessBuilder($arguments, $this->processFactory);
    }
}
