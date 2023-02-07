<?php
declare(strict_types=1);

namespace YamlFormatter\Collection;

use YamlFormatter\Formatted;
use YamlFormatter\FormattedNamed;
use YamlFormatter\FormattedWrapper;
use YamlFormatter\Stringer\FormattedStringer;

abstract class FormattedDict extends FormattedCollection
{
    /** @var FormattedNamed[] */
    private array $values = [];

    abstract protected function fmtKey(int|string $key): string;

    public function asYaml(): string
    {
        $wrapper = new class extends FormattedWrapper {
            public function stringer(FormattedStringer $stringer): string
            {
                return self::empty($stringer);
            }

            public function named(FormattedNamed $named): string
            {
                return self::empty($named);
            }
        };

        return implode(
            PHP_EOL,
            array_map(
                static fn(FormattedNamed $value) => $wrapper->wrap($value),
                $this->values,
            ),
        );
    }

    public function add(int|string $key, Formatted $value): self
    {
        $this->values[] = new FormattedNamed($this->indent, $this->fmtKey($key), $value);
        return $this;
    }

    public function merge(self $other): self
    {
        $this->values = array_merge($this->values, $other->values);
        return $this;
    }
}
