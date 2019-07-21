<?php

namespace DMT\Test\Serializer\Stream;

use DMT\Serializer\Stream\Serializer;
use DMT\Test\Serializer\Stream\Fixtures\Car;
use Doctrine\Common\Annotations\AnnotationRegistry;
use JMS\Serializer\SerializerBuilder;
use Metadata\Tests\Driver\Fixture\C\SubDir\C;
use PHPUnit\Framework\TestCase;

class SerializerTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        AnnotationRegistry::registerUniqueLoader('class_exists');
    }

    public function testDeserializeXml()
    {
        $streamSerializer = new Serializer(SerializerBuilder::create()->build());
        $collection = $streamSerializer->deserialize(__DIR__ . '/Fixtures/cars.xml', Car::class, 'xml', '/cars/car');

        foreach ($collection as $car) {
            $this->assertInstanceOf(Car::class, $car);
        }
    }

    public function testDeserializeJson()
    {
        $streamSerializer = new Serializer(SerializerBuilder::create()->build());
        $collection = $streamSerializer->deserialize(__DIR__ . '/Fixtures/cars.json', Car::class, 'json', 'cars');

        foreach ($collection as $car) {
            $this->assertInstanceOf(Car::class, $car);
        }
    }

    public function testSerializeJson()
    {
        $car = new Car();
        $car->name = 'Honda';
        $car->models = ['Civic'];

        $otherCar = new Car();
        $otherCar->name = 'Saab';
        $otherCar->models = ['96', '9000'];

        $streamSerializer = new Serializer(SerializerBuilder::create()->build());
        $streamSerializer->serialize('php://output', new \ArrayIterator([$car, $otherCar]), 'json');

        $this->expectOutputString('[{"name":"Honda","models":["Civic"]},{"name":"Saab","models":["96","9000"]}]');
    }
}
