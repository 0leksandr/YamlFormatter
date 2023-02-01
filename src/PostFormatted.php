<?php

declare(strict_types=1);

namespace YamlFormatter;

use YamlFormatter\Collection\FormattedCollection;
use YamlFormatter\Collection\FormattedDict;
use YamlFormatter\Collection\FormattedList;
use YamlFormatter\Stringer\FormattedStringer;

abstract class PostFormatted
{
    abstract protected function stringer(FormattedStringer $stringer): string;

    abstract protected function named(FormattedNamed $named): string;

    public function format(Formatted $formatted): string
    {
        if ($formatted instanceof FormattedStringer) {
            return $this->stringer($formatted);
        } elseif ($formatted instanceof FormattedNamed) {
            return $this->named($formatted);
        } elseif ($formatted instanceof FormattedCollection) {
            return $this->newline($formatted);
        } else {
            throw new \RuntimeException('fuck');
        }
    }

    protected function newline(Formatted $formatted): string
    {
//        return PHP_EOL . $formatted->prefix() . $formatted->asYaml();
        return PHP_EOL . $formatted->asYaml();
    }

    protected function space(Formatted $formatted): string
    {
        return ' ' . $formatted->asYaml();
    }

    protected function empty(Formatted $formatted): string
    {
        return $formatted->asYaml();
    }
}
