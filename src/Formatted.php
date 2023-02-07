<?php
declare(strict_types=1);

namespace YamlFormatter;

abstract class Formatted
{
    public const TAB = '    ';

    public function __construct(
        protected int $indent
    ) {
    }

    abstract public function asYaml(): string;

    abstract public function wrappedBy(FormattedWrapper $formattedWrapper): string;
}
