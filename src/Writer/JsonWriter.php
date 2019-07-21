<?php

namespace DMT\Serializer\Stream\Writer;

use DMT\Serializer\Stream\Handler\Json\PrepareWriter;
use Exception;
use RuntimeException;
use SplFileObject;
use Traversable;

/**
 * Class JsonWriter
 *
 * @package DMT\Serializer\Stream
 */
class JsonWriter implements WriterInterface
{
    /** @var SplFileObject */
    protected $handler;
    /** @var string */
    private $file = 'php://memory';
    /** @var PrepareWriter */
    private $writeHandler;

    /**
     * JsonWriter constructor.
     *
     * @param SplFileObject|null $handler
     *
     * @throws RuntimeException
     */
    public function __construct(SplFileObject $handler = null)
    {
        $this->handler = $handler ?? $this->createHandler();
    }

    /**
     * Close the stream/file handle.
     *
     * @return void
     */
    public function close(): void
    {
        $this->writeHandler = null;
        $this->file = 'php://memory';
    }

    /**
     * Get the internal write handler.
     *
     * @return SplFileObject
     */
    public function getWriteHandler()
    {
        return $this->handler;
    }

    /**
     * Open the stream or file.
     *
     * @param string $streamUriOrFile
     *
     * @return void
     * @throws RuntimeException
     */
    public function open(string $streamUriOrFile): void
    {
        if ($this->file !== $streamUriOrFile) {
            $this->file = $streamUriOrFile;
            $this->createHandler();
        }
    }

    /**
     * Prepare the internal writer.
     *
     * @param string|null $objectsPath The destination of the objects to store.
     * @param string $contents The document where the objects will be stored in.
     *
     * @return void
     * @throws RuntimeException
     */
    public function prepare(string $objectsPath = null, ?string $contents = '[]'): void
    {
        $this->writeHandler = new PrepareWriter($objectsPath, $contents);
    }

    /**
     * Write the objects to file or stream.
     *
     * @param Traversable|string[] $objectCollection
     *
     * @return void
     * @throws RuntimeException
     */
    public function write(Traversable $objectCollection): void
    {
        $this->writeHandler->handle($this, function () use ($objectCollection) {
            $processed = 0;
            foreach ($objectCollection as $object) {
                if ($processed++ > 0) {
                    $this->handler->fwrite(',');
                }
                if (!$this->handler->fwrite($object)) {
                    throw new RuntimeException('Could not write json');
                }
            }
        });
    }

    /**
     * Set new file handler.
     *
     * @return SplFileObject
     * @throws RuntimeException
     */
    protected function createHandler(): SplFileObject
    {
        try {
            return $this->handler = new SplFileObject($this->file, 'w');
        } catch (Exception $exception) {
            throw new RuntimeException('Could not open file', 0, $exception);
        }
    }
}