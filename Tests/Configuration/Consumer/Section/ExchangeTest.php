<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Tests\Configuration\Consumer\Section;

use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Consumer\Section\Exchange;

class ExchangeTest extends \PHPUnit_Framework_TestCase
{
    public function testSection()
    {
        $input = [
            'name' => 'my-name',
            'type' => 'direct',
            'autodelete' => true,
            'durable' => true,
        ];

        $section = new Exchange($input);

        $this->assertEquals('exchange', $section->getName());
        $this->assertEquals($input, $section->getProperties());
    }
}
