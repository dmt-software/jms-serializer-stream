<?php

namespace DMT\Serializer\Stream\Reader;

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
    protected $handler;

    /**
     * JsonReader constructor.
     *
     * @param JsonReaderHandler $handler
     */
    public function __construct(JsonReaderHandler $handler = null)
    {
        $this->handler = $handler ?? new JsonReaderHandler;
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
     * @param string|null $objectsPath The path within the stream or file where the objects are retrieved from.
     *
     * @return Generator
     * @throws RuntimeException
     */
    public function read(string $objectsPath = null): Generator
    {
        try {
            yield from $this->items($objectsPath);
        } catch (Exception $exception) {
            throw new RuntimeException('Error reading json', 0, $exception);
        }
    }

    /**
     * Get items from json.
     *
     * @param string|null $objectsPath
     *
     * @return Generator
     * @throws Exception
     */
    protected function items(?string $objectsPath): Generator
    {
        $depth = $this->prepare($objectsPath);
        $processed = 0;

        do {
            yield $processed++ => json_encode($this->handler->value());
        } while ($this->handler->next() && $this->handler->depth() > $depth);
    }

    /**
     * @param string|null $objectsPath
     *
     * @return int
     * @throws Exception
     */
    protected function prepare(?string $objectsPath): int
    {
        if (!$objectsPath) {
            $this->handler->read();

            if ($this->handler->type() === JsonReaderHandler::ARRAY) {
                $this->handler->read();
            };

            return 0;
        }

        $paths = explode('.', $objectsPath);

        foreach ($paths as $depth => $path) {
            while ($this->handler->read($path)) {
                if ($depth + 1 === $this->handler->depth()) {
                    break;
                }
            }
        }

        $depth = $this->handler->depth();
        $this->handler->read();

        return $depth;
    }
}
