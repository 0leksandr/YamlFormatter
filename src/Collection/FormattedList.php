<?php

declare(strict_types=1);

namespace YamlFormatter\Collection;

use YamlFormatter\Formatted;

final class FormattedList extends FormattedCollection
{
    protected function linePrefix(): string
    {
        return '-';
    }

    public function add(Formatted $value): self
    {
        return $this->addValue($value);
    }
}
