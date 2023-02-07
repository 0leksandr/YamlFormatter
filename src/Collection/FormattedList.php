<?php
declare(strict_types=1);

namespace YamlFormatter\Collection;

use YamlFormatter\Formatted;
use YamlFormatter\FormattedNamed;
use YamlFormatter\FormattedWrapper;
use YamlFormatter\Stringer\FormattedStringer;

final class FormattedList extends FormattedCollection
{
    /** @var Formatted[] */
    private array $values = [];

    public function asYaml(): string
    {
        $wrapper = new class extends FormattedWrapper {
            public function stringer(FormattedStringer $stringer): string
            {
                return self::space($stringer);
            }

            public function named(FormattedNamed $named): string
            {
                return self::space($named);
            }
        };

        return implode(
            PHP_EOL,
            array_map(
                static fn(Formatted $value) => '-' . $wrapper->wrap($value),
                $this->values,
            ),
        );
    }

    public function add(Formatted $value): self
    {
        $this->values[] = $value;
        return $this;
    }
}
