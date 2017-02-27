<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Generator;

interface RabbitMqConfigGeneratorInterface
{
    /**
     * Generate all supervisor worker configuration files
     */
    public function generate();
}
