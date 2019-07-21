<?php

namespace DMT\Serializer\Stream\Writer;

use RuntimeException;
use Traversable;

interface WriterInterface
{
    /**
     * Close the stream/file handle.
     */
    public function close(): void;

    /**
     * Get the internal write handler.
     *
     * @return mixed
     */
    public function getWriteHandler();

    /**
     * Open the stream or file.
     *
     * @param string $streamUriOrFile
     *
     * @return void
     * @throws RuntimeException
     */
    public function open(string $streamUriOrFile): void;

    /**
     * Prepare the internal writer.
     *
     * @param string $objectsPath The destination of the objects to store.
     * @param string $contents The document where the objects will be stored in.
     *
     * @return void
     * @throws RuntimeException
     */
    public function prepare(string $objectsPath, string $contents = ''): void;

    /**
     * Write the objects to file or stream.
     *
     * @param Traversable|string[] $objectCollection
     *
     * @return void
     * @throws RuntimeException
     */
    public function write(Traversable $objectCollection): void;
}