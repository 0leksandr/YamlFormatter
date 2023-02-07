<?php
declare(strict_types=1);

namespace YamlFormatter;

use YamlFormatter\Stringer\FormattedStringer;

final class FormattedNamed extends Formatted
{
    public function __construct(
        int $indent,
        private string $name,
        private Formatted $value,
    ) {
        parent::__construct($indent);
    }

    public function asYaml(): string
    {
        $wrapper = new class extends FormattedWrapper {
            public function stringer(FormattedStringer $stringer): string
            {
                return self::space($stringer);
            }

            public function named(FormattedNamed $named): string
            {
                return self::newline($named);
            }
        };

        return "{$this->name}:{$wrapper->wrap($this->value)}";
    }

    public function isNamed(): bool
    {
        return true;
    }

    public function wrappedBy(FormattedWrapper $formattedWrapper): string
    {
        return $formattedWrapper->named($this);
    }

    protected function isMultiline(): bool
    {
        return $this->value->isNamed() || $this->value->isMultiline();
    }
}
