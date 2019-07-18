<?php

namespace DMT\Serializer\Stream\Reader\Handler;

use pcrov\JsonReader\Exception;
use pcrov\JsonReader\JsonReader as JsonReaderHandler;
use RuntimeException;
use TypeError;

/**
 * Class JsonPreparationHandler
 *
 * @package DMT\Serializer\Stream
 */
class JsonPreparationHandler implements ReaderHandlerInterface
{
    /**
     * Prepare the json stream/file.
     *
     * @param JsonReaderHandler $reader The internal json reader.
     * @param string|null $objectsPath The path where the reader should point to.
     */
    public function handle($reader, string $objectsPath = null): void
    {
        try {
            if ($objectsPath) {
                $this->handleObjectsPath($reader, $objectsPath);
            } else {
                $this->handleEmptyObjectsPath($reader);
            }
        } catch (TypeError $error) {
            throw new RuntimeException('Incompatible reader for this handler');
        } catch (Exception $exception) {
            throw new RuntimeException('Error preparing json', 0, $exception);
        }
    }

    /**
     * Set the pointer (from current position) to the given path.
     *
     * @param JsonReaderHandler $reader The file handle reader.
     * @param string $objectsPath The path where the objects are located.
     *
     * @return void
     * @throws Exception
     */
    protected function handleObjectsPath(JsonReaderHandler $reader, string $objectsPath): void
    {
        $paths = explode('.', $objectsPath);

        foreach ($paths as $depth => $path) {
            while ($reader->read($path)) {
                if ($depth + 1 === $reader->depth()) {
                    break;
                }
            }
        }

        $reader->read();
    }

    /**
     * Set the pointer (from current position) to the first eligible object.
     *
     * @param JsonReaderHandler $reader
     * @throws Exception
     */
    protected function handleEmptyObjectsPath(JsonReaderHandler $reader): void
    {
        $reader->read();

        if ($reader->type() === JsonReaderHandler::ARRAY) {
            $reader->read();
        };
    }
}