<?php

namespace DMT\Serializer\Stream\Reader;

use Generator;
use RuntimeException;

/**
 * Interface ReaderInterface
 *
 * @package DMT\Serializer\Stream
 */
interface ReaderInterface
{
    /**
     * Close the file handle.
     *
     * @return void
     */
    public function close(): void;

    /**
     * Get the internal read handler.
     *
     * @return mixed
     */
    public function getReadHandler();

    /**
     * Open a stream (wrapper) or file.
     *
     * @param string $streamUriOrFile
     *
     * @throws RuntimeException
     */
    public function open(string $streamUriOrFile);

    /**
     * Set the pointer to the objects to read.
     *
     * @param string|null $objectsPath  The path within the stream or file where the objects are retrieved from.
     *
     * @return mixed
     * @throws RuntimeException
     */
    public function prepare(string $objectPath = null);

    /**
     * Read the file one piece at a time.
     *
     * @return Generator
     * @throws RuntimeException
     */
    public function read(): Generator;
}