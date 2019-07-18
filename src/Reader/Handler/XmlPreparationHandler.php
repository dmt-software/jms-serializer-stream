<?php

namespace DMT\Serializer\Stream\Reader\Handler;

use function PHPSTORM_META\elementType;
use RuntimeException;
use Throwable;
use TypeError;
use XMLReader as XmlReaderHandler;

class XmlPreparationHandler implements ReaderHandlerInterface
{
    /**
     * Handle the file/stream.
     *
     * @param mixed $reader The internal reader for ReaderInterface.
     * @param string|null $objectsPath The path of the objects where the reader should point to.
     *
     * @return void
     * @throws RuntimeException
     */
    public function handle($reader, string $objectsPath = null): void
    {
        try {

            if (!$objectsPath) {
                $this->handleEmptyObjectsPath($reader);
            } else {
                $this->handleObjectsPath($reader, $objectsPath);
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
     * @param string $objectsPath The path where the objects are located.
     */
    protected function handleObjectsPath(XmlReaderHandler $reader, string $objectsPath)
    {
        $reader->read();

        $paths = preg_split('~/~', $objectsPath, -1, PREG_SPLIT_NO_EMPTY);
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
        $reader->read();

        while ($reader->nodeType !== XmlReaderHandler::ELEMENT) {
            $reader->read();

            if ($reader->nodeType === XmlReaderHandler::NONE) {
                throw new RuntimeException('Could not read from xml');
            }
        }
    }
}