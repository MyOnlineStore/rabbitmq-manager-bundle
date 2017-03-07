<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Process;

use Symfony\Component\Process\ProcessBuilder as SymfonyProcessBuilder;

final class ProcessBuilder extends SymfonyProcessBuilder implements ProcessBuilderInterface
{
    /**
     * @var ProcessFactoryInterface
     */
    private $processFactory;

    /**
     * @param array                   $arguments
     * @param ProcessFactoryInterface $processFactory
     */
    public function __construct($arguments = [], ProcessFactoryInterface $processFactory)
    {
        parent::__construct($arguments);

        $this->processFactory = $processFactory;
    }

    /**
     * @inheritdoc
     */
    public function getProcess()
    {
        return $this->processFactory->createFromProcess(
            parent::getProcess()
        );
    }
}
