<?php

namespace DMT\Serializer\Stream;

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
     * Open a stream (wrapper) or file.
     *
     * @param string $streamUriOrFile
     *
     * @throws RuntimeException
     */
    public function open(string $streamUriOrFile);

    /**
     * Read the file one piece at a time.
     *
     * @param string|null $objectsPath  The path within the stream or file where the objects are retrieved from.
     *
     * @return Generator
     * @throws RuntimeException
     */
    public function read(string $objectsPath = null): Generator;
}