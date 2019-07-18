<?php

namespace DMT\Serializer\Stream\Reader\Handler;

use RuntimeException;

interface ReaderHandlerInterface
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
    public function handle($reader, string $objectsPath = null): void;
}