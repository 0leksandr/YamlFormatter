<?php

declare(strict_types=1);

namespace YamlFormatter\Collection;

use YamlFormatter\Formatted;
use YamlFormatter\FormattedNamed;
use YamlFormatter\PostFormatted;
use YamlFormatter\Stringer\FormattedStringer;

final class FormattedList extends FormattedCollection
{
    public function asYaml(): string
    {
        $postFormatter = new class extends PostFormatted {
            protected function stringer(FormattedStringer $stringer): string
            {
                return self::space($stringer);
            }

            protected function named(FormattedNamed $named): string
            {
                return self::space($named);
            }
        };
        $lines = [];
        foreach ($this->values as $value) {
            $lines[] = "{$this->prefix()}-{$postFormatter->format($value)}";
        }
        return implode(PHP_EOL, $lines);
    }

    public function add(Formatted $value): self
    {
        return $this->addValue($value);
    }
}
