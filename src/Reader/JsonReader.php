<?php

namespace DMT\Serializer\Stream\Reader;

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
    protected $handler;

    /**
     * JsonReader constructor.
     *
     * @param JsonReaderHandler $reader
     */
    public function __construct(JsonReaderHandler $reader = null)
    {
        $this->handler = $reader ?? new JsonReaderHandler;
    }

    /**
     * Get the internal read handler
     *
     * @return JsonReaderHandler
     */
    public function getReadHandler()
    {
        return $this->handler;
    }

    /**
     * Close the file handle.
     *
     * @return void
     */
    public function close(): void
    {
        $this->handler->close();
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
            $this->handler->open($streamUriOrFile);
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
        $depth = max($this->handler->depth() - 1, 0);
        $processed = 0;

        do {
            yield $processed++ => json_encode($this->handler->value());
        } while ($this->handler->next() && $this->handler->depth() > $depth);
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
        (new Handler\JsonPreparationHandler($objectsPath))->handle($this);
    }
}
