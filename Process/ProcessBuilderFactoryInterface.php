<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Process;

interface ProcessBuilderFactoryInterface
{
    /**
     * @param array $arguments
     *
     * @return ProcessBuilderInterface
     */
    public function create(array $arguments = []);
}
