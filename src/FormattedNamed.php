<?php

declare(strict_types=1);

namespace YamlFormatter;

use YamlFormatter\Collection\FormattedDict;
use YamlFormatter\Collection\FormattedList;
use YamlFormatter\Stringer\FormattedStringer;

final class FormattedNamed extends Formatted
{
    /** @var string */
    private $name;
    /** @var Formatted */
    private $value;

    public function __construct(int $indent, string $name, Formatted $value)
    {
        parent::__construct($indent);
        $this->name = $name;
        $this->value = $value;
    }

    public function asYaml(): string
    {
        $postFormatter = new class extends PostFormatted {
            protected function stringer(FormattedStringer $stringer): string
            {
                return $this->space($stringer);
            }

            protected function named(FormattedNamed $named): string
            {
                return $this->newline($named);
            }

            protected function list(FormattedList $list): string
            {
                return $this->newline($list);
            }

            protected function dict(FormattedDict $dict): string
            {
                return $this->newline($dict);
            }

            private function space(Formatted $formatted): string
            {
                return ' ' . $formatted->asYaml();
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
