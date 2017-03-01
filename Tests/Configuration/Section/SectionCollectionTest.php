<?php

namespace MyOnlineStore\Bundle\RabbitMqManagerBundle\Tests\Configuration\Section;

use MyOnlineStore\Bundle\RabbitMqManagerBundle\Configuration\Section\SectionCollection;
use Supervisor\Configuration\Section;

class SectionCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testCollection()
    {
        $section1 = $this->getMock(Section::class);
        $section1->expects(self::once())->method('getName')->willReturn('section1');
        $section1->expects(self::once())->method('getProperties')->willReturn(['foo' => 'bar']);
        $section2 = $this->getMock(Section::class);
        $section2->expects(self::once())->method('getName')->willReturn('section2');
        $section2->expects(self::once())->method('getProperties')->willReturn(['hello' => 'world']);
        $section3 = $this->getMock(Section::class);
        $section3->expects(self::exactly(2))->method('getName')->willReturn('section3');
        $section3->expects(self::exactly(2))->method('getProperties')->willReturn(['john' => 'doe']);

        $collection = new SectionCollection([$section1, $section2]);

        $collection->addSection($section3);

        $this->assertEquals([
            'section1' => ['foo' => 'bar'],
            'section2' => ['hello' => 'world'],
            'section3' => ['john' => 'doe'],
        ], $collection->toArray());

        $collection->addSection($section3);

        $this->assertEquals([
            'section1' => ['foo' => 'bar'],
            'section2' => ['hello' => 'world'],
            'section3' => ['john' => 'doe'],
        ], $collection->toArray());
    }
}
