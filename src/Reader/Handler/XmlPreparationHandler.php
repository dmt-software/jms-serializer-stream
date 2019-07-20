<?php

namespace DMT\Serializer\Stream\Reader\Handler;

use RuntimeException;
use Throwable;
use TypeError;
use XMLReader as XmlReaderHandler;

class XmlPreparationHandler implements ReaderHandlerInterface
{
    /**
     * @var string|null
     */
    protected $objectsPath;

    /**
     * XmlPreparationHandler constructor.
     *
     * @param string|null $objectsPath The path of the objects where the reader should point to.
     */
    public function __construct(string $objectsPath = null)
    {
        $this->objectsPath = $objectsPath;
    }

    /**
     * Handle the file/stream.
     *
     * @param mixed $reader The internal reader for ReaderInterface.
     *
     * @return void
     * @throws RuntimeException
     */
    public function handle($reader): void
    {
        try {
            $reader->read();

            if (!$this->objectsPath) {
                $this->handleEmptyObjectsPath($reader);
            } else {
                $this->handleObjectsPath($reader);
            }
        } catch (TypeError $error) {
            throw new RuntimeException('Incompatible reader for this handler');
        } catch (Throwable $error) {
            throw new RuntimeException('Error preparing xml', 0, $error);
        }
    }

    /**
     * Set the pointer to the objects path.
     *
     * @param XmlReaderHandler $reader The internal reader.
     */
    protected function handleObjectsPath(XmlReaderHandler $reader)
    {
        $paths = preg_split('~/~', $this->objectsPath, -1, PREG_SPLIT_NO_EMPTY);
        $stack = [];

        do {
            if ($reader->nodeType === XmlReaderHandler::END_ELEMENT) {
                array_pop($stack);
            } elseif ($reader->nodeType === XmlReaderHandler::ELEMENT) {
                array_push($stack, $reader->localName);
            }

            if ($paths == $stack) {
                break;
            }
        } while ($reader->read() !== false);
    }

    /**
     * Set pointer to first xml element.
     *
     * @param XmlReaderHandler $reader The internal reader.
     * @throws RuntimeException
     */
    protected function handleEmptyObjectsPath(XmlReaderHandler $reader): void
    {
        while ($reader->nodeType !== XmlReaderHandler::ELEMENT) {
            $reader->read();

            if ($reader->nodeType === XmlReaderHandler::NONE) {
                throw new RuntimeException('Could not read from xml');
            }
        }
    }
}