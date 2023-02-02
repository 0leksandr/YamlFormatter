<?php

declare(strict_types=1);

namespace YamlFormatter\Collection;

use YamlFormatter\Formatted;
use YamlFormatter\FormattedNamed;
use YamlFormatter\PostFormatted;
use YamlFormatter\Stringer\FormattedStringer;

abstract class FormattedDict extends FormattedCollection
{
    abstract protected function fmtKey($key): string;

    public function asYaml(): string
    {
        $postFormatter = new class extends PostFormatted {
            protected function stringer(FormattedStringer $stringer): string
            {
                return self::empty($stringer);
            }

            protected function named(FormattedNamed $named): string
            {
                return self::empty($named);
            }
        };
        $lines = [];
        foreach ($this->values as $value) {
            $lines[] = "{$this->prefix()}{$postFormatter->format($value)}";
        }
        return implode(PHP_EOL, $lines);
    }

    /**
     * @param mixed $key
     * @return $this
     */
    public function add($key, Formatted $value): self
    {
        return $this->addValue(new FormattedNamed($this->indent, $this->fmtKey($key), $value));
    }

    /**
     * @return $this
     */
    public function merge(self $other): self
    {
        return $this->mergeCollection($other);
    }
}
