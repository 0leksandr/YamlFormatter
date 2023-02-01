<?php

declare(strict_types=1);

namespace YamlFormatter\Collection;

final class FormattedClass extends FormattedDict
{
    /**
     * @param mixed $key
     */
    protected function fmtKey($key): string
    {
        return (string)$key;
    }
}
