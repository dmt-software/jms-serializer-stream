<?php

namespace DMT\Serializer\Stream\Reader\Handler;

use RuntimeException;

interface ReaderHandlerInterface
{
    /**
     * Handle the file/stream.
     *
     * @param mixed $reader The internal reader for ReaderInterface.
     *
     * @return void
     * @throws RuntimeException
     */
    public function handle($reader): void;
}