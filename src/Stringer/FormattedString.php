<?php

declare(strict_types=1);

namespace YamlFormatter\Stringer;

use YamlFormatter\Formatter;

final class FormattedString extends FormattedStringer
{
    /** @var string */
    private $string;

    public function __construct(int $indent, string $string)
    {
        parent::__construct($indent);
        $this->string = $string;
    }

    public function asYaml(): string
    {
        if ($this->isMultiline()) {
            $lineLen = $this->lineLen();
            $n = PHP_EOL;
            $prefix = $this->prefix();
            return '>'
                . $n
                . implode(
                    $n,
                    array_map(
                        static function (string $line) use ($prefix): string {
                            return $prefix . $line;
                        },
                        array_merge(
                            ...array_map(
                                static function (string $line) use ($lineLen): array {
                                    return str_split($line, $lineLen);
                                },
                                preg_split("/\R/", trim($this->string))
                            )
                        )
                    )
                );
        } else {
            return $this->asString();
        }
    }

    public function isNamed(): bool
    {
        return false;
    }

    protected function isMultiline(): bool
    {
        $lineLen = $this->lineLen();
        return 20 < $lineLen && $lineLen < strlen($this->asString());
    }

    public function asString(): string
    {
        $formatted = '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $this->string) . '"';
        if (is_numeric($this->string)) {
            $int = (int)$this->string;
            if (Formatter::isTimestamp($int)) {
                $datetime = Formatter::fmtTimestamp($int);
                $formatted .= " <{$datetime}>";
            }
        }

        return $formatted;
    }

    public function delimiter(): string
    {
        return ' ';
    }

    private function lineLen(): int
    {
        return 100 - $this->indent * strlen($this->prefix());
    }
}
