<?php

declare(strict_types=1);

namespace YamlFormatter\Stringer;

use YamlFormatter\Formatted;

abstract class FormattedStringer extends Formatted
{
    abstract public function asString(): string;
}
