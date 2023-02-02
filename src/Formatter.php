<?php

declare(strict_types=1);

namespace YamlFormatter;

use Closure;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use ReflectionClass;
use Throwable;
use YamlFormatter\Collection\FormattedArray;
use YamlFormatter\Collection\FormattedClass;
use YamlFormatter\Collection\FormattedDict;
use YamlFormatter\Collection\FormattedList;
use YamlFormatter\Stringer\FormattedLiteral;
use YamlFormatter\Stringer\FormattedString;

use function count;
use function dirname;
use function get_class;
use function in_array;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_object;
use function is_resource;
use function is_string;

class Formatter
{
    /** @var mixed */
    private $value;
    /** @var string[] */
    private $formattedObjectIds = [];

    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function format(int $indent): Formatted
    {
        return $this->fmtValue($this->value, $indent);
    }

    /**
     * @param array<string, mixed> $trace
     */
    public static function fmtTrace(array $trace): FormattedLiteral
    {
        if (($file = $trace['file'] ?? null) && ($line = $trace['line'] ?? null)) {
            return self::fmtFileLine($file, $line);
        } elseif ($function = $trace['function'] ?? null) {
            if (($class = $trace['class'] ?? null) && ($type = $trace['type'] ?? null)) {
                if ($type === '->') {
                    $type = '::';
                }
                return new FormattedLiteral(str_replace('\\', '/', $class) . $type . $function);
            } else {
                return new FormattedLiteral($function);
            }
        } else {
            // TODO: alert
            return new FormattedLiteral(print_r($trace, true));
        }
    }

    public static function fmtFileLine(string $file, int $line): FormattedLiteral
    {
        if (preg_match('~^([\w/.]+)\((\\d+)\) : eval\(\)\'d code$~', $file, $matches)) {
            [$file, $line] = [$matches[1], (int)($matches[2])];
        }

        $root = self::getProjectRoot();
        $file = preg_replace("~^{$root}/~", '', $file);

        return new FormattedLiteral("{$file}:{$line}");
    }

    public static function getProjectRoot(): string
    {
        $dir = __DIR__;
        while (true) {
            if ($dir === '/') {
                // TODO: exception
                die(__FILE__ . ':' . __LINE__ . ': cannot define project root');
            }
            if (file_exists($dir . '/.git/')) {
                return $dir;
            }
            $dir = dirname($dir);
        }
    }

    public static function defaultTimezone(): DateTimeZone
    {
        return new DateTimeZone('Europe/Kiev');
    }

    public static function fmtTimestamp(int $timestamp): string
    {
        return self
            ::fmtDatetime(
                (new DateTimeImmutable())
                    ->setTimestamp($timestamp)
                    ->setTimezone(self::defaultTimezone())
            )
            ->asString();
    }

    public static function isTimestamp(int $int): bool
    {
        // expected humanity self-destruction (or singularity) date
        return (strtotime('2000-01-01') < $int) && ($int < strtotime('2050-01-01'));
    }

    /**
     * @param mixed $value
     */
    private function fmtValue($value, int $indent): Formatted
    {
        if ($value === VOID) {
            return new FormattedLiteral('');
        } elseif ($value === null) {
            return new FormattedLiteral('null');
        } elseif (is_bool($value)) {
            return $value ? new FormattedLiteral('true') : new FormattedLiteral('false');
        } elseif (is_int($value)) {
            return self::fmtInt($value);
        } elseif (is_float($value)) {
            return self::fmtFloat($value);
        } elseif (is_string($value)) {
            return $this->fmtString($value, $indent);
        } elseif (is_array($value)) {
            if (!$value) {
                return new FormattedLiteral('[]');
            } elseif (self::isList($value)) {
                return $this->fmtList($value, $indent);
            } else {
                return $this->fmtArray($value, $indent);
            }
        } elseif (is_resource($value)) {
            return $this->fmtResource($value, $indent);
        } elseif (is_object($value)) {
            if ($recursion = $this->checkRecursion($value)) {
                return $recursion;
            } elseif ($value instanceof Closure) {
                return new FormattedLiteral('closure');
            } elseif ($value instanceof DateTimeInterface) {
                return self::fmtDatetime($value);
            } elseif ($value instanceof Formatted) {
                return $value;
            } elseif ($value instanceof Throwable) {
                return $this->fmtThrowable($value, $indent);
            } else {
                return new FormattedNamed(
                    $indent,
                    self::getClassName($value),
                    $this->fmtObjectProperties(
                        $value,
                        [
                            'yii\base\ErrorHandler::_memoryReserve',
                            'yii\db\BaseActiveRecord::_oldAttributes',
                        ],
                        $indent + 1
                    )
                );
            }
        } else {
            // TODO: alert?
            return new FormattedLiteral(print_r($value, true));
        }
    }

    private function fmtList(array $list, int $indent): FormattedList
    {
        $formatted = new FormattedList($indent);
        foreach ($list as $item) {
            $formatted->add($this->fmtValue($item, $indent + 1));
        }

        return $formatted;
    }

    /**
     * @param array<int|string, mixed> $array
     */
    private function fmtArray(array $array, int $indent): FormattedArray
    {
        $formatted = new FormattedArray($indent);
        foreach ($array as $key => $item) {
            $formatted->add($key, $this->fmtValue($item, $indent + 1));
        }

        return $formatted;
    }

    private function fmtAsClassProperties(array $array, int $indent): FormattedClass
    {
        $formatted = new FormattedClass($indent);
        foreach ($array as $key => $item) {
            $formatted->add($key, $this->fmtValue($item, $indent + 1));
        }

        return $formatted;
    }

    private static function fmtInt(int $int): FormattedLiteral
    {
        $str = (string)$int;
        if (self::isTimestamp($int)) {
            $datetime = self::fmtTimestamp($int);
            $str .= " <{$datetime}>";
        }

        return new FormattedLiteral($str);
    }

    private static function fmtFloat(float $float): FormattedLiteral
    {
        $str = (string)$float;
        if (strpos($str, '.') === false && !is_infinite($float)) {
            $str .= '.0';
        }

        return new FormattedLiteral($str);
    }

    private function fmtString(string $string, int $indent): Formatted
    {
        $json = json_decode($string, true);
        if (is_array($json)) {
            return new FormattedNamed($indent, 'JSON', $this->fmtArray($json, $indent + 1));
        }

        return new FormattedString($indent, $string);
    }

    private static function fmtDatetime(DateTimeInterface $dateTime): FormattedLiteral
    {
        return new FormattedLiteral($dateTime->format('Y-m-d H:i:s e'));
    }

    private function fmtThrowable(Throwable $throwable, int $indent): FormattedClass
    {
        $formattedTrace = (new FormattedList($indent))
            ->add(self::fmtFileLine($throwable->getFile(), $throwable->getLine()));
        foreach ($throwable->getTrace() as $trace) {
            $formattedTrace->add(static::fmtTrace($trace));
        }

        return (new FormattedClass($indent))
            ->merge(
                $this->fmtAsClassProperties(
                    array_filter([
                        'exception' => new FormattedLiteral(self::getClassName($throwable)),
                        'message' => $throwable->getMessage(),
                        'code' => $throwable->getCode(),
                        'trace' => $formattedTrace,
                        'previous' => $throwable->getPrevious(),
                    ]),
                    $indent
                )
            )
            ->merge(
                $this->fmtObjectProperties(
                    $throwable,
                    [
                        'message',
                        'code',
                        'file',
                        'line',

                        'Exception::string',
                        'Exception::trace',
                        'Exception::previous',

                        'Error::string',
                        'Error::trace',
                        'Error::previous',
                    ],
                    $indent
                )
            );
    }

    /**
     * @param object $object
     * @param string[] $ignoredProperties
     */
    private function fmtObjectProperties(
        $object,
        array $ignoredProperties,
        int $indent
    ): FormattedClass {
        $array = new FormattedClass($indent);
        foreach ((array)$object as $propertyName => $propertyValue) {
            /** @var string $formattedPropertyName */
            $formattedPropertyName = preg_replace(
                ['/^~\*~/', '/^(~class@anonymous~)[^~]+~/', '/^~([^~]+)~/'],
                ['', '$1', '$1::'],
                str_replace("\0", '~', $propertyName)
            );
            if (!in_array($formattedPropertyName, $ignoredProperties, true)) {
                $array->add(
                    $formattedPropertyName,
                    $this->fmtValue($propertyValue, $indent + 1)
                );
            }
        }

        return $array;
    }

    /**
     * @param resource $resource
     */
    private function fmtResource($resource, int $indent): Formatted
    {
        $type = get_resource_type($resource);
        if ($type === 'stream') {
            /** @noinspection NestedPositiveIfStatementsInspection */
            if (stream_get_meta_data($resource)['mode'] === 'r') {
                /** @noinspection NestedPositiveIfStatementsInspection */
                if ($size = ((array)(fstat($resource)))['size'] ?? null) {
                    /** @noinspection NestedPositiveIfStatementsInspection */
                    if ($size < 1e6) {
                        return $this->fmtValue(stream_get_contents($resource), $indent);
                    }
                }
            }
        }

        return new FormattedLiteral('resource@' . $type);
    }

    private static function isList(array $array): bool
    {
        return array_keys($array) === range(0, count($array) - 1);
    }

    /**
     * @param object $object
     */
    private static function getClassName($object): string
    {
        if ((new ReflectionClass($object))->isAnonymous()) {
            return 'anonymous';
        }

        return get_class($object);
    }

    /**
     * @param object $object
     */
    private function checkRecursion($object): ?FormattedLiteral
    {
        $id = spl_object_hash($object);
        if (in_array($id, $this->formattedObjectIds, true)) {
            return new FormattedLiteral(self::getClassName($object) . " [recursion@$id]");
        }
        $this->formattedObjectIds[] = $id;

        return null;
    }
}
