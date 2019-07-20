<?php

namespace DMT\Serializer\Stream\Reader;

use DMT\Serializer\Stream\Reader\Handler\JsonPreparationHandler;
use DMT\Serializer\Stream\ReaderInterface;
use Generator;
use pcrov\JsonReader\Exception;
use pcrov\JsonReader\JsonReader as JsonReaderHandler;
use RuntimeException;

/**
 * Class JsonReader
 *
 * @package DMT\Serializer\Stream
 */
class JsonReader implements ReaderInterface
{
    /** @var JsonReaderHandler */
    protected $reader;

    /**
     * JsonReader constructor.
     *
     * @param JsonReaderHandler $reader
     */
    public function __construct(JsonReaderHandler $reader = null)
    {
        $this->reader = $reader ?? new JsonReaderHandler;
    }

    /**
     * Close the file handle.
     *
     * @return void
     */
    public function close(): void
    {
        $this->reader->close();
    }

    /**
     * Open a stream (wrapper) or file.
     *
     * @param string $streamUriOrFile
     *
     * @throws RuntimeException
     */
    public function open(string $streamUriOrFile)
    {
        try {
            $this->reader->open($streamUriOrFile);
        } catch (Exception $exception) {
            throw new RuntimeException("Could not read from {$streamUriOrFile}", 0, $exception);
        }
    }

    /**
     * Read the file one piece at a time.
     *
     * @return Generator
     * @throws RuntimeException
     */
    public function read(): Generator
    {
        try {
            yield from $this->items();
        } catch (Exception $exception) {
            throw new RuntimeException('Error reading json', 0, $exception);
        }
    }

    /**
     * Get items from json.
     *
     * @return Generator
     * @throws Exception
     */
    protected function items(): Generator
    {
        $depth = max($this->reader->depth() - 1, 0);
        $processed = 0;

        do {
            yield $processed++ => json_encode($this->reader->value());
        } while ($this->reader->next() && $this->reader->depth() > $depth);
    }

    /**
     * Set the pointer to the object to read.
     *
     * @param string|null $objectsPath The path within the stream or file where the objects are retrieved from.
     *
     * @return void
     * @throws RuntimeException
     */
    public function prepare(string $objectsPath = null): void
    {
        (new JsonPreparationHandler($objectsPath))->handle($this->reader);
    }
}
