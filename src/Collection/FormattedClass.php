<?php

declare(strict_types=1);

namespace YamlFormatter\Collection;

final class FormattedClass extends FormattedDict
{
    protected function fmtKey($key): string
    {
        return (string)$key;
    }
}
