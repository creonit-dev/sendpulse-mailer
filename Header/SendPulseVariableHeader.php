<?php

namespace Creonit\SendPulseMailer\Header;

use Symfony\Component\Mime\Header\UnstructuredHeader;

class SendPulseVariableHeader extends UnstructuredHeader
{
    protected $key;

    public function __construct(string $name, string $value)
    {
        $this->key = $name;

        parent::__construct("X-SendPulse-Variable-{$name}", $value);
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }
}