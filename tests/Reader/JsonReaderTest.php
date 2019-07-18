<?php

namespace DMT\Test\Serializer\Stream\Reader;

use DMT\Serializer\Stream\Reader\JsonReader;
use DMT\Test\Serializer\Stream\Fixtures\Cars;
use pcrov\JsonReader\JsonReader as JsonReaderHandler;
use PHPUnit\Framework\TestCase;

class JsonReaderTest extends TestCase
{
    public function testReadObjectsPath()
    {
        $cars = new Cars();

        $reader = new JsonReader();
        $reader->open($cars->asFileStream(Cars::TYPE_JSON));
        $reader->prepare('cars');

        foreach ($reader->read('cars') as $key => $row) {
            $this->assertSame($cars->getJsonParts('cars')[$key], $row);
        }

        $reader->close();
    }

    public function testReadFull()
    {
        $cars = new Cars();

        $reader = new JsonReader();
        $reader->open($cars->asFileStream(Cars::TYPE_JSON));
        $reader->prepare();

        $this->assertSame($cars->getJson(), $reader->read()->current());

        $reader->close();
    }

    public function testReadArray()
    {
        $data = [
            ['name' => 'foo', 'id' => 3],
            ['name' => 'bar', 'id' => 1],
            ['name' => 'baz', 'id' => 2],
        ];

        $handler = new JsonReaderHandler(JsonReaderHandler::FLOATS_AS_STRINGS);
        $handler->json(json_encode($data));

        $reader = new JsonReader($handler);
        $reader->prepare();

        foreach ($reader->read() as $key => $row) {
            $this->assertSame($data[$key], json_decode($row, true));
        }

        $reader->close();
    }
}
