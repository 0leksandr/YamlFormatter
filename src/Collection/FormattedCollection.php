<?php
declare(strict_types=1);

namespace YamlFormatter\Collection;

use YamlFormatter\Formatted;
use YamlFormatter\FormattedWrapper;

abstract class FormattedCollection extends Formatted
{
    public function wrappedBy(FormattedWrapper $formattedWrapper): string
    {
        return $formattedWrapper::newline($this);
    }
}
