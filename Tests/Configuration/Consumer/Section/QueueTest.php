<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Tests\Configuration\Consumer\Section;

use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Consumer\Section\Queue;

class QueueTest extends \PHPUnit_Framework_TestCase
{
    public function testSection()
    {
        $input = [
            'routingkey' => 'my-route',
        ];

        $section = new Queue($input);

        $this->assertEquals('queuesettings', $section->getName());
        $this->assertEquals($input, $section->getProperties());
    }
}
