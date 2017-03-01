<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Tests\Configuration\Consumer\Section;

use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Consumer\Section\Rabbitmq;

class RabbitmqTest extends \PHPUnit_Framework_TestCase
{
    public function testSection()
    {
        $input = [
            'host' => 'hostname',
            'username' => 'guest',
            'password' => 'guest',
            'vhost' => '/',
            'port' => 24234,
            'queue' => 'my-queue',
            'compression' => true,
        ];

        $section = new Rabbitmq($input);

        $this->assertEquals('rabbitmq', $section->getName());
        $this->assertEquals($input, $section->getProperties());
    }
}
