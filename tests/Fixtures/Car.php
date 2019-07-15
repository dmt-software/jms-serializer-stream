<?php

namespace DMT\Test\Serializer\Stream\Fixtures;

use JMS\Serializer\Annotation as JMS;

class Car
{
    /**
     * @JMS\Type("string")
     * @JMS\XmlElement()
     *
     * @var string
     */
    public $name;

    /**
     * @JMS\Type("array<string>")
     * @JMS\XmlList(inline=false, entry="model")
     *
     * @var array
     */
    public $models;
}