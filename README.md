# Stream serializer

## Usage

### Serialize

```php
<?php

use DMT\Serializer\Stream\Serializer;
use JMS\Serializer\Serializer as JmsSerializer;

/** @var JmsSerializer $jmsSerializer */
$serializer = new Serializer($jmsSerializer);

/** @var Traversable|Car[] $collection */
$serializer->serialize('file://path/cars.json', $collection, 'json', 'cars', '{"cars":[]}');

// file://path/cars.json contains a json string containing all cars from collection
```

### Deserialize 

```php
<?php
 
use DMT\Serializer\Stream\Serializer;
use JMS\Serializer\Serializer as JmsSerializer;

/** @var JmsSerializer $jmsSerializer */
$serializer = new Serializer($jmsSerializer);
$collection = $serializer->deserialize('file://path/cars.xml', Car::class, '/cars/car', 'xml');

/** $collection is now a Generator that returns deserialized Car objects */
foreach ($collection as $key => $car) {
    // -- use the car
}
```