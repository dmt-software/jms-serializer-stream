<?php

namespace DMT\Serializer\Stream;

use DMT\Serializer\Stream\Reader\JsonReader;
use DMT\Serializer\Stream\Reader\ReaderInterface;
use DMT\Serializer\Stream\Reader\XmlReader;
use DMT\Serializer\Stream\Writer\JsonWriter;
use JMS\Serializer\Exception\Exception as JmsException;
use JMS\Serializer\Serializer as JmsSerializer;
use RuntimeException;
use Traversable;

/**
 * Class Serializer
 *
 * @package DMT\Serializer\Stream
 */
class Serializer
{
    /** @var JmsSerializer */
    protected $serializer;

    /** @var array */
    protected $deserializationReaders = [
        'json' => JsonReader::class,
        'xml' => XmlReader::class,
    ];

    /**
     * Serializer constructor.
     *
     * @param JmsSerializer $serializer
     */
    public function __construct(JmsSerializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param string $uri
     * @param Traversable $objects
     * @param string $format
     * @param string|null $toPath
     * @param string|null $container
     */
    public function serialize(string $uri, Traversable $objects, string $format, string $toPath = null, string $container = null): void
    {
        $writer = $this->getSerializeWriter($format);
        $writer->open($uri);
        $writer->prepare($toPath, $container);

        try {
            $writer->write(call_user_func(function () use ($objects, $format) {
                    foreach ($objects as $object) {
                        yield $this->serializer->serialize($object, $format);
                    }
                })
            );
        } finally {
            $writer->close();
        }

    }

    /**
     * Deserialize objects from a file/stream
     *
     * @param string $uri The file or stream wrapper to read from.
     * @param string $toObject The class name of object that is returned.
     * @param string $format The deserialization format.
     * @param string $fromPath The path within the stream or file where the objects are retrieved from.
     *
     * @return Traversable|{$deserializeObject}[]
     * @throws RuntimeException
     */
    public function deserialize(string $uri, string $toObject, string $format, string $fromPath = null): Traversable
    {
        $reader = $this->getDeserializationReader($format);
        $reader->open($uri);
        $reader->prepare($fromPath);

        try {
            foreach ($reader->read() as $key => $value) {
                yield $this->serializer->deserialize($value, $toObject, $format);
            }
        } catch (JmsException $exception) {
            throw new RuntimeException("Error deserialize object {$toObject}", 0, $exception->getMessage());
        } finally {
            $reader->close();
        }
    }

    /**
     * Get deserialization reader.
     *
     * @param string $format The format for the reader.
     *
     * @return ReaderInterface
     * @throws RuntimeException
     */
    protected function getDeserializationReader(string $format): ReaderInterface
    {
        if (!array_key_exists($format, $this->deserializationReaders)) {
            throw new RuntimeException("No reader for format: {$format}");
        }

        if (!is_a($this->deserializationReaders[$format], ReaderInterface::class, true)) {
            throw new RuntimeException('Illegal reader given');
        }

        if (is_string($this->deserializationReaders[$format])) {
            return new $this->deserializationReaders[$format];
        }

        return clone($this->deserializationReaders[$format]);
    }

    protected function getSerializeWriter(string $format)
    {
        return new JsonWriter();
    }
}