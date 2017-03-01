<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Consumer\Section;

use Supervisor\Configuration\Section\Base;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Exchange extends Base
{
    /**
     * @inheritdoc
     */
    protected $name = 'exchange';

    /**
     * @inheritdoc
     */
    protected function configureProperties(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined('name')
            ->setAllowedTypes('name', 'string');

        $resolver
            ->setDefined('type')
            ->setAllowedTypes('type', 'string');

        $resolver
            ->setDefined('autodelete')
            ->setAllowedTypes('autodelete', 'bool');

        $resolver
            ->setDefined('durable')
            ->setAllowedTypes('durable', 'bool');
    }
}
