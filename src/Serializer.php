<?php

namespace DMT\Serializer\Stream;

use DMT\Serializer\Stream\Reader\JsonReader;
use DMT\Serializer\Stream\Reader\XmlReader;
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
     * @param array $deserializeReaders
     */
    public function __construct(JmsSerializer $serializer, array $deserializeReaders = null)
    {
        $this->serializer = $serializer;

        if ($deserializeReaders) {
            $this->deserializationReaders = $deserializeReaders;
        }
    }

    /**
     * Deserialize objects from a file/stream
     *
     * @param string $uri The file or stream wrapper to read from.
     * @param string $toObject The class name of object that is returned.
     * @param string $fromPath The path within the stream or file where the objects are retrieved from.
     * @param string $format The deserialization format.
     *
     * @return Traversable|{$deserializeObject}[]
     * @throws RuntimeException
     */
    public function deserialize(string $uri, string $toObject, string $fromPath, string $format): Traversable
    {
        $reader = $this->getDeserializationReader($format);
        $reader->open($uri);

        try {
            foreach ($reader->read($fromPath) as $key => $value) {
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
}