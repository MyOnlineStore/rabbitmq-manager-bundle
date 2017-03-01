<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Consumer\Section;

use Supervisor\Configuration\Section\Base;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Logs extends Base
{
    /**
     * @inheritdoc
     */
    protected $name = 'logs';

    /**
     * @inheritdoc
     */
    protected function configureProperties(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined('error')
            ->setAllowedTypes('error', 'string');

        $resolver
            ->setDefined('info')
            ->setAllowedTypes('info', 'string');
    }
}
