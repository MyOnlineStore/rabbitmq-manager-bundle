<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Tests\Configuration\Consumer\Section;

use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Consumer\Section\Prefetch;

class PrefetchTest extends \PHPUnit_Framework_TestCase
{
    public function testSection()
    {
        $input = [
            'count' => 234,
            'global' => true,
        ];

        $section = new Prefetch($input);

        $this->assertEquals('prefetch', $section->getName());
        $this->assertEquals($input, $section->getProperties());
    }
}
