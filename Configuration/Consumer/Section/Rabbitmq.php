<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Consumer\Section;

use Supervisor\Configuration\Section\Base;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Rabbitmq extends Base
{
    /**
     * @inheritdoc
     */
    protected $name = 'rabbitmq';

    /**
     * @inheritdoc
     */
    protected function configureProperties(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined('host')
            ->setAllowedTypes('host', 'string');

        $resolver
            ->setDefined('username')
            ->setAllowedTypes('username', 'string');

        $resolver
            ->setDefined('password')
            ->setAllowedTypes('password', 'string');

        $resolver
            ->setDefined('vhost')
            ->setAllowedTypes('vhost', 'string');

        $resolver
            ->setDefined('queue')
            ->setAllowedTypes('queue', 'string');

        $resolver
            ->setDefined('port')
            ->setAllowedTypes('port', 'int');

        $resolver
            ->setDefined('compression')
            ->setAllowedTypes('compression', 'bool');
    }
}
