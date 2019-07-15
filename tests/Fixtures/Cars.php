<?php

namespace DMT\Test\Serializer\Stream\Fixtures;

use SimpleXMLElement;

/**
 * Class Cars
 *
 * @package DMT\Serializer\Stream
 */
class Cars
{
    public const TYPE_JSON = 'json';
    public const TYPE_XML = 'xml';

    /**
     * Get the cars xml.
     *
     * @return string
     */
    public function getXml(): string
    {
        return file_get_contents(__DIR__ . '/cars.xml');
    }

    /**
     * Get a list of xml fragments.
     *
     * @param string $path
     * @return array|string[]
     */
    public function getXmlParts(string $path): array
    {
        return array_map(
            function (SimpleXMLElement $element) {
                return $element->asXML();
            },
            (new SimpleXMLElement($this->getXml()))->xpath($path)
        );
    }

    /**
     * Get the cars json.
     *
     * @return string
     */
    public function getJson(): string
    {
        return json_encode(json_decode(file_get_contents(__DIR__ . '/cars.json')));
    }

    /**
     * Get a list of items from json.
     *
     * @param string $path
     * @return array
     */
    public function getJsonParts(string $path): array
    {
        $paths = explode('.', $path);

        $json = json_decode($this->getJson(), true);

        foreach ($paths as $path) {
            $json = $json[$path] ?? [];
        }

        return array_map('json_encode', $json);
    }

    /**
     * Get contents as file stream.
     *
     * @param string $type
     * @return string
     */
    public function asFileStream(string $type): string
    {
        $file = tempnam(sys_get_temp_dir(), $type . '-');
        $fh = fopen($file, 'r+');

        switch ($type) {
            case static::TYPE_JSON:
                fputs($fh, $this->getJson());
                break;
            case static::TYPE_XML:
                fputs($fh, $this->getXml());
        }

        fclose($fh);

        return $file;
    }
}