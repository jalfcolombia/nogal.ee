<?php

namespace NogalEE\DataType;

class DateTime extends \DateTime
{
    private $format;

    public function __construct(string $time = null, string $format = null, \DateTimeZone $timezone = null)
    {
        parent::__construct($time, $timezone);
        $this->format = $format;
    }

    public function setFormat(string $format): self
    {
        $this->format = $format;
        return $this;
    }

    public function __toString(): string
    {
        return ($this->format === null) ? $this->format : $this->format($this->format);
    }
}
