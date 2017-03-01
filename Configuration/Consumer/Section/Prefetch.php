<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Consumer\Section;

use Supervisor\Configuration\Section\Base;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Prefetch extends Base
{
    /**
     * @inheritdoc
     */
    protected $name = 'prefetch';

    /**
     * @inheritdoc
     */
    protected function configureProperties(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined('count')
            ->setAllowedTypes('count', 'int');

        $resolver
            ->setDefined('global')
            ->setAllowedTypes('global', 'bool');
    }
}
