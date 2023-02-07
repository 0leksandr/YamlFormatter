<?php
declare(strict_types=1);

namespace YamlFormatter\Stringer;

use YamlFormatter\Formatted;
use YamlFormatter\FormattedWrapper;

abstract class FormattedStringer extends Formatted
{
    abstract public function asString(): string;

    public function wrappedBy(FormattedWrapper $formattedWrapper): string
    {
        return $formattedWrapper->stringer($this);
    }
}
