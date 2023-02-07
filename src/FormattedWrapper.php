<?php
declare(strict_types=1);

namespace YamlFormatter;

use YamlFormatter\Stringer\FormattedStringer;

abstract class FormattedWrapper
{
    abstract public function stringer(FormattedStringer $stringer): string;

    abstract public function named(FormattedNamed $named): string;

    public function wrap(Formatted $formatted): string
    {
        return $formatted->wrappedBy($this);
    }

    public static function newline(Formatted $formatted): string
    {
        $n = PHP_EOL;
        $prefix = $formatted->prefix();
        $lines = array_map(
            static fn(string $line) => $prefix . $line,
            explode($n, $formatted->asYaml()),
        );

        return $n . implode($n, $lines);
    }

    protected static function space(Formatted $formatted): string
    {
        return ' ' . $formatted->asYaml();
    }

    protected static function empty(Formatted $formatted): string
    {
        return $formatted->asYaml();
    }
}
