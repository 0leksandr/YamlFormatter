<?php
declare(strict_types=1);

namespace YamlFormatter\Collection;

final class FormattedClass extends FormattedDict
{
    protected function fmtKey(int|string $key): string
    {
        return (string)$key;
    }
}
