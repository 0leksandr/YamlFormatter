<?php
declare(strict_types=1);

namespace YamlFormatter\Collection;

use YamlFormatter\Formatted;

abstract class FormattedCollection extends Formatted
{
    public function isNamed(): bool
    {
        return false;
    }

    protected function isMultiline(): bool
    {
        return true;
    }
}
