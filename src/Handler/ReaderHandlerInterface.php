<?php

namespace DMT\Serializer\Stream\Handler;

use DMT\Serializer\Stream\Reader\ReaderInterface;
use RuntimeException;

interface ReaderHandlerInterface
{
    /**
     * Handle the file/stream.
     *
     * @param ReaderInterface $reader
     *
     * @return void
     * @throws RuntimeException
     */
    public function handle(ReaderInterface $reader): void;
}