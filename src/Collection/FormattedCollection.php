<?php
declare(strict_types=1);

namespace YamlFormatter\Collection;

use YamlFormatter\Formatted;
use YamlFormatter\FormattedWrapper;

abstract class FormattedCollection extends Formatted
{
    public function isNamed(): bool
    {
        return false;
    }

    public function wrappedBy(FormattedWrapper $formattedWrapper): string
    {
        return $formattedWrapper::newline($this);
    }

    protected function isMultiline(): bool
    {
        return true;
    }
}
