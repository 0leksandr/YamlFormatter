<?php

declare(strict_types=1);

namespace YamlFormatter\Collection;

use YamlFormatter\Formatter;
use YamlFormatter\Stringer\FormattedString;
use YamlFormatter\Stringer\FormattedStringer;

final class FormattedArray extends FormattedDict
{
    protected function fmtKey($key): string
    {
        $keyFormatted = (new Formatter($key))->format(0);
        if (!$keyFormatted instanceof FormattedStringer) {
            $keyFormatted = new FormattedString(0, (string)$key);
        }
        return $keyFormatted->asString();
    }
}
