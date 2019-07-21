<?php

namespace DMT\Serializer\Stream\Handler\Json;

use DMT\Serializer\Stream\Handler\WriterHandlerInterface;
use DMT\Serializer\Stream\Writer\WriterInterface;
use RuntimeException;

/**
 * Class JsonPreparation
 *
 * @package DMT\Serializer\Stream
 */
class PrepareWriter implements WriterHandlerInterface
{
    /** @var string */
    protected $objectsPath;
    /** @var string */
    protected $contents = '[]';

    /**
     * JsonPreparation constructor.
     *
     * @param string|null $objectsPath
     * @param string $contents
     */
    public function __construct(string $objectsPath = null, string $contents = null)
    {
        $this->objectsPath = $objectsPath;
        $this->contents = $contents ?? '[]';
    }

    /**
     * Handle the wrtie stream.
     *
     * @param WriterInterface $writer
     *
     * @return void
     * @throws RuntimeException
     */
    public function handle(WriterInterface $writer, callable $next): void
    {
        $paths = explode('.', $this->objectsPath);
        $lastPath = end($paths);

        $regex = '~^(';
        if ($this->objectsPath) {
            foreach ($paths as $path) {
                if ($path === $lastPath) {
                    $regex .= sprintf('\{.*"%s"\s*:\s*', $lastPath);
                } else {
                    $regex .= sprintf('.*\{.*"%s"\s*:.*', $path);
                }
            }
        }
        $regex .= '\[)(.*)$~';

        $match = [];
        if (!preg_match($regex, $this->contents, $match)) {
            throw new RuntimeException('Could not find objects location');
        }

        $writer->getWriteHandler()->fwrite($match[1]);

        call_user_func($next);

        $writer->getWriteHandler()->fwrite($match[2]);
    }
}