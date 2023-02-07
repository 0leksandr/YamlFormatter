<?php
declare(strict_types=1);

namespace YamlFormatter;

use YamlFormatter\Collection\FormattedCollection;
use YamlFormatter\Stringer\FormattedStringer;

abstract class PostFormatted // MAYBE: Prefixed, PostFormatter, FormattedWrapper
{
    abstract protected function stringer(FormattedStringer $stringer): string;

    abstract protected function named(FormattedNamed $named): string;

    public function format(Formatted $formatted): string
    {
        if ($formatted instanceof FormattedStringer) { // TODO: refactor
            return $this->stringer($formatted);
        } elseif ($formatted instanceof FormattedNamed) {
            return $this->named($formatted);
        } elseif ($formatted instanceof FormattedCollection) {
            return self::newline($formatted);
        } else {
            throw new \RuntimeException('fuck');
        }
    }

    protected static function newline(Formatted $formatted): string
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
