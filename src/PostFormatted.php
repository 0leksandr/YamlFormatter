<?php

declare(strict_types=1);

namespace YamlFormatter;

use YamlFormatter\Collection\FormattedDict;
use YamlFormatter\Collection\FormattedList;
use YamlFormatter\Stringer\FormattedStringer;

abstract class PostFormatted
{
    abstract protected function stringer(FormattedStringer $stringer): string;

    abstract protected function named(FormattedNamed $named): string;

    abstract protected function list(FormattedList $list): string;

    abstract protected function dict(FormattedDict $dict): string;

    public function format(Formatted $formatted): string
    {
        if ($formatted instanceof FormattedStringer) {
            return $this->stringer($formatted);
        } elseif ($formatted instanceof FormattedNamed) {
            return $this->named($formatted);
        } elseif ($formatted instanceof FormattedList) {
            return $this->list($formatted);
        } elseif ($formatted instanceof FormattedDict) {
            return $this->dict($formatted);
        } else {
            throw new \RuntimeException('fuck');
        }
    }

    protected function newline(Formatted $formatted): string
    {
        return PHP_EOL . $formatted->prefix() . $formatted->asYaml();
    }
}
