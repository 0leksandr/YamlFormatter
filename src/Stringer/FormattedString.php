<?php
declare(strict_types=1);

namespace YamlFormatter\Stringer;

use YamlFormatter\Formatter;

final class FormattedString extends FormattedStringer
{
    public function __construct(
        int $indent,
        private string $string,
    ) {
        parent::__construct($indent);
    }

    public function asYaml(): string
    {
        if ($this->isMultiline()) {
            $lineLen = $this->lineLen();
            $n = PHP_EOL;
            return '>'
                . $n
                . implode(
                    $n,
                    array_map(
                        static fn(string $line) => self::TAB . $line,
                        array_merge(...array_map(
                            static fn(string $line) => str_split($line, $lineLen),
                            preg_split("/\R/", trim($this->string)),
                        )),
                    ),
                );
        } else {
            return $this->asString();
        }
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

    private function isMultiline(): bool
    {
        $lineLen = $this->lineLen();
        return 20 < $lineLen && $lineLen < strlen($this->asString());
    }

    private function lineLen(): int
    {
        return 100 - $this->indent * strlen(self::TAB);
    }
}
