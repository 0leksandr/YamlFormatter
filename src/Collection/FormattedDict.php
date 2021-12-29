<?php

declare(strict_types=1);

namespace YamlFormatter\Collection;

use YamlFormatter\Formatted;
use YamlFormatter\FormattedNamed;

abstract class FormattedDict extends FormattedCollection
{
    abstract protected function fmtKey($key): string;

    protected function linePrefix(): string
    {
        return '';
    }

    public function add(string $key, Formatted $value): self
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
