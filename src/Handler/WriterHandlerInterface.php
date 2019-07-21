<?php

namespace DMT\Serializer\Stream\Handler;

use DMT\Serializer\Stream\Writer\WriterInterface;
use RuntimeException;

/**
 * Interface WriterHandlerInterface
 *
 * @package DMT\Serializer\Stream
 */
interface WriterHandlerInterface
{
    /**
     * Handle the write stream.
     *
     * @param WriterInterface $writer
     * @param callable $next
     *
     * @return void
     * @throws RuntimeException
     */
    public function handle(WriterInterface $writer, callable $next): void;
}