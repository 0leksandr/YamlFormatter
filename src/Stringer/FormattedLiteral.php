<?php
declare(strict_types=1);

namespace YamlFormatter\Stringer;

final class FormattedLiteral extends FormattedStringer
{
    public function __construct(
        private string $value,
    ) {
        parent::__construct(0);
    }

    public function asYaml(): string
    {
        return $this->value;
    }

    public function isNamed(): bool
    {
        return false;
    }

    protected function isMultiline(): bool
    {
        return false;
    }

    public function asString(): string
    {
        return $this->value;
    }
}
