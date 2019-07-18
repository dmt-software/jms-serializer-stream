<?php

namespace DMT\Test\Serializer\Stream\Reader;

use DMT\Serializer\Stream\Reader\XmlReader;
use DMT\Test\Serializer\Stream\Fixtures\Cars;
use PHPUnit\Framework\TestCase;

class XmlReaderTest extends TestCase
{
    public function testReadObjectsPath()
    {
        $cars = new Cars();

        $reader = new XmlReader();
        $reader->open($cars->asFileStream(Cars::TYPE_XML));
        $reader->prepare('/cars/car');

        foreach ($reader->read('/cars/car') as $key => $car) {
            $this->assertEquals($cars->getXmlParts('/cars/car')[$key], $car);
        }

        $reader->close();
    }

    public function testReadFull()
    {
        $cars = new Cars();

        $reader = new XmlReader();
        $reader->open($cars->asFileStream(Cars::TYPE_XML));
        $reader->prepare();

        $this->assertSame($cars->getXml(), $reader->read()->current());

        $reader->close();
    }
}
