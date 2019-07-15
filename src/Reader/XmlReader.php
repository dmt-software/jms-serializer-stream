<?php

namespace DMT\Serializer\Stream\Reader;

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
    protected $handler;

    /**
     * XmlReader constructor.
     *
     * @param XmlReaderHandler|null $handler
     */
    public function __construct(XmlReaderHandler $handler = null)
    {
        $this->handler = $handler ?? new XmlReaderHandler;
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
        $previous = libxml_disable_entity_loader(false);
        $stream = strpos($streamUriOrFile, '://') ? $streamUriOrFile : "file://$streamUriOrFile";

        try {
            $this->handler->open($stream);
        } catch (Throwable $error) {
            throw new RuntimeException("Could not read from {$streamUriOrFile}", 0, $error);
        } finally {
            libxml_disable_entity_loader($previous);
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
        } catch (Throwable $error) {
            throw new RuntimeException('error reading xml', 0, $error);
        }
    }

    /**
     * Get items from xml.
     *
     * @param string|null $objectsPath
     * @return Generator
     */
    protected function items(?string $objectsPath): Generator
    {
        $node = $this->prepare($objectsPath);
        $processed = 0;

        do {
            if (!$xml = $this->handler->readOuterXml()) {
                throw new RuntimeException(libxml_get_last_error()->message);
            }
            yield $processed++ => $xml;
        } while ($this->handler->next($node) !== false);
    }

    /**
     * Set pointer to the element defined by objectsPath
     *
     * @param string|null $objectsPath A full (x)path of the element to iterate from.
     * @return string|null
     */
    protected function prepare(?string $objectsPath): ?string
    {
        $this->handler->read();

        if (!$objectsPath) {
            while ($this->handler->nodeType !== XmlReaderHandler::ELEMENT) {
                $this->handler->read();

                if ($this->handler->nodeType === XmlReaderHandler::NONE) {
                    throw new RuntimeException('Could not read from xml');
                }
            }

            return $this->handler->localName;
        }

        $paths = preg_split('~/~', $objectsPath, -1, PREG_SPLIT_NO_EMPTY);
        $stack = [];

        do {
            if ($this->handler->nodeType === XmlReaderHandler::END_ELEMENT) {
                array_pop($stack);
            } elseif ($this->handler->nodeType === XmlReaderHandler::ELEMENT) {
                array_push($stack, $this->handler->localName);
            }

            if ($paths == $stack) {
                break;
            }
        } while ($this->handler->read() !== false);

        return $this->handler->localName;
    }
}
