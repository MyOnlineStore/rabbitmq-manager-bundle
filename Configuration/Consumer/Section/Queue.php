<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Consumer\Section;

use Supervisor\Configuration\Section\Base;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Queue extends Base
{
    /**
     * @inheritdoc
     */
    protected $name = 'queuesettings';

    /**
     * @inheritdoc
     */
    protected function configureProperties(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined('routingkey')
            ->setAllowedTypes('routingkey', 'string');
    }
}
