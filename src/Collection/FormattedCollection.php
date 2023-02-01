<?php

declare(strict_types=1);

namespace YamlFormatter\Collection;

use YamlFormatter\Formatted;

abstract class FormattedCollection extends Formatted
{
    /** @var Formatted[] */
    protected $values = [];

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
