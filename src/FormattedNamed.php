<?php

declare(strict_types=1);

namespace YamlFormatter;

use YamlFormatter\Stringer\FormattedStringer;

final class FormattedNamed extends Formatted
{
    public function __construct(
        int $indent,
        private string $name,
        private Formatted $value,
    ) {
        parent::__construct($indent);
    }

    public function asYaml(): string
    {
        $postFormatter = new class extends PostFormatted {
            protected function stringer(FormattedStringer $stringer): string
            {
                return self::space($stringer);
            }

            protected function named(FormattedNamed $named): string
            {
                return self::newline($named);
            }
        };

        return "{$this->name}:{$postFormatter->format($this->value)}";
    }

    public function isNamed(): bool
    {
        return true;
    }

    protected function isMultiline(): bool
    {
        return $this->value->isNamed() || $this->value->isMultiline();
    }
}
