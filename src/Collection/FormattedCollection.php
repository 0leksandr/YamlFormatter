<?php

declare(strict_types=1);

namespace YamlFormatter\Collection;

use YamlFormatter\Formatted;
use YamlFormatter\FormattedNamed;
use YamlFormatter\PostFormatted;
use YamlFormatter\Stringer\FormattedStringer;

abstract class FormattedCollection extends Formatted
{
    /** @var Formatted[] */
    private $values;

    abstract protected function linePrefix(): string;

    public function asYaml(): string
    {
        $postFormatter = new class extends PostFormatted {
            protected function stringer(FormattedStringer $stringer): string
            {
                return $this->empty($stringer);
            }

            protected function named(FormattedNamed $named): string
            {
                return $this->empty($named);
            }

            protected function list(FormattedList $list): string
            {
                return $this->newline($list);
            }

            protected function dict(FormattedDict $dict): string
            {
                return $this->newline($dict);
            }

            private function empty(Formatted $formatted): string
            {
                return $formatted->asYaml();
            }
        };
        $lines = [];
        foreach ($this->values as $value) {
            $lines[] = "{$this->prefix()}{$this->linePrefix()}{$postFormatter->format($value)}";
        }
        return implode(PHP_EOL, $lines);
    }

    public function isNamed(): bool
    {
        return false;
    }

    protected function isMultiline(): bool
    {
        return true;
    }

    /**
     * @return $this
     */
    protected function addValue(Formatted $value): self
    {
        $this->values[] = $value;
        return $this;
    }

    /**
     * @return $this
     */
    protected function mergeCollection(self $other): self
    {
        $this->values = array_merge($this->values, $other->values);
        return $this;
    }
}
