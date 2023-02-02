<?php

declare(strict_types=1);

namespace YamlFormatter;

abstract class Formatted
{
    public function __construct(
        protected int $indent
    ) {
    }

    abstract public function asYaml(): string;

    abstract public function isNamed(): bool;

    abstract protected function isMultiline(): bool;

    public function delimiter(): string // TODO: prefix
    {
        return $this->isMultiline() ? PHP_EOL : ' ';
    }

    public function prefix(): string
    {
        $tab = '    ';
        return implode(array_fill(0, $this->indent, $tab));
    }
}
