<?php

namespace DMT\Test\Serializer\Stream\Writer;

use DMT\Serializer\Stream\Writer\JsonWriter;
use DMT\Test\Serializer\Stream\Fixtures\Cars;
use PHPUnit\Framework\TestCase;

class JsonWriterTest extends TestCase
{
    public function testWrite()
    {
        $cars = new Cars();

        $writer = new JsonWriter(new \SplFileObject('php://output', 'w'));
        $writer->prepare();
        $writer->write($cars->getCarsAsJson());

        $this->setOutputCallback(function($response) use ($cars) {
            foreach ($cars->getJsonParts('cars') as $jsonPart) {
                $this->assertContains($jsonPart, $response);
            }
        });
    }

    public function testWriteToObjectPath()
    {
        $cars = new Cars();

        $writer = new JsonWriter(new \SplFileObject('php://output', 'w'));
        $writer->prepare('cars', '{"brands":3,"types":8,"cars":[]}');
        $writer->write($cars->getCarsAsJson());

        $this->expectOutputString(json_decode(json_encode($cars->getJson())));
    }
}
