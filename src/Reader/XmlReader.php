<?php

namespace DMT\Serializer\Stream\Reader;

use DMT\Serializer\Stream\Reader\Handler\XmlPreparationHandler;
use DMT\Serializer\Stream\ReaderInterface;
use Generator;
use RuntimeException;
use Throwable;
use XMLReader as XmlReaderHandler;

/**
 * Class XmlReader
 *
 * @package DMT\Serializer\Stream
 */
class XmlReader implements ReaderInterface
{
    /** @var XmlReaderHandler */
    protected $reader;
    /** @var XmlPreparationHandler */
    protected $handler;

    /**
     * XmlReader constructor.
     *
     * @param XmlReaderHandler|null $reader
     */
    public function __construct(XmlReaderHandler $reader = null, XmlPreparationHandler $handler = null)
    {
        $this->reader = $reader ?? new XmlReaderHandler;
        $this->handler = $handler ?? new XmlPreparationHandler();
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
        $previous = libxml_disable_entity_loader(false);
        $stream = strpos($streamUriOrFile, '://') ? $streamUriOrFile : "file://$streamUriOrFile";

        try {
            $this->reader->open($stream);
        } catch (Throwable $error) {
            throw new RuntimeException("Could not read from {$streamUriOrFile}", 0, $error);
        } finally {
            libxml_disable_entity_loader($previous);
        }
    }

    /**
     * Set pointer to the element defined by objectsPath
     *
     * @param string|null $objectsPath A full (x)path of the element to iterate from.
     * @return void
     */
    public function prepare(string $objectsPath = null): void
    {
        $this->handler->handle($this->reader, $objectsPath);
    }

    /**
     * Read the file one piece at a time.
     *
     * @param string|null $objectsPath A full (x)path of the element to iterate from.
     *
     * @return Generator
     * @throws RuntimeException
     */
    public function read(string $objectsPath = null): Generator
    {
        try {
            yield from $this->items();
        } catch (Throwable $error) {
            throw new RuntimeException('error reading xml', 0, $error);
        }
    }

    /**
     * Get items from xml.
     *
     * @return Generator
     */
    protected function items(): Generator
    {
        $processed = 0;

        do {
            if (!$xml = $this->reader->readOuterXml()) {
                $message = libxml_get_last_error() ? libxml_get_last_error()->message : 'ObjectsPath not found';
                throw new RuntimeException($message);
            }
            yield $processed++ => $xml;
        } while ($this->reader->next($this->reader->localName) !== false);
    }
}
