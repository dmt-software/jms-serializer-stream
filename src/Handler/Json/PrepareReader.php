<?php

namespace DMT\Serializer\Stream\Handler\Json;

use DMT\Serializer\Stream\Handler\ReaderHandlerInterface;
use DMT\Serializer\Stream\Reader\ReaderInterface;
use pcrov\JsonReader\Exception;
use pcrov\JsonReader\JsonReader as JsonReaderHandler;
use RuntimeException;
use TypeError;

/**
 * Class JsonPreparation
 *
 * @package DMT\Serializer\Stream
 */
class PrepareReader implements ReaderHandlerInterface
{
    /**
     * @var string|null
     */
    protected $objectsPath;

    /**
     * JsonPreparation constructor.
     *
     * @param string|null $objectsPath The path where the reader should point to.
     */
    public function __construct(string $objectsPath = null)
    {
        $this->objectsPath = $objectsPath;
    }

    /**
     * Prepare the json stream/file.
     *
     * @param ReaderInterface $reader The internal json reader.
     * @throws RuntimeException
     */
    public function handle(ReaderInterface $reader): void
    {
        try {
            if ($this->objectsPath) {
                $this->handleObjectsPath($reader->getReadHandler());
            } else {
                $this->handleEmptyObjectsPath($reader->getReadHandler());
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
     *
     * @return void
     * @throws Exception
     */
    protected function handleObjectsPath(JsonReaderHandler $reader): void
    {
        $paths = explode('.', $this->objectsPath);

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