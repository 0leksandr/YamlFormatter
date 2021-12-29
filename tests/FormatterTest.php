<?php

declare(strict_types=1);

namespace YamlFormatter;

use Closure;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use PHPUnit\Framework\TestCase;

use function is_array;

class FormatterTest extends TestCase
{
    /**
     * @return array[]
     */
    public function dataProvider(): array
    {
        return [
            ['test', 'test'],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function test($value, string $expectedFormatted): void
    {
        self::assertEquals($expectedFormatted, (new Formatter($value))->format(0)->asYaml());
    }

    /**
     * @throws Exception
     */
    public function _test(): void
    {
        self::assertEquals(
            [
                'text' => 'test',
                'null' => 'null',
                'true' => 'true',
                'false' => 'false',
                'int' => '-13',
                'float' => '6.66',
                'big-float' => '-1.23e+45',
                'inf' => 'INF',
                'string-123' => '123',
                'int-123' => '123',
                'float-123' => '123',
                'datetime-immutable' => '2021-04-27 19:09:20 America/New_York',
                'datetime-with-timezone-boston' => '2021-04-27 19:09:20 EST',
                'datetime-with-timezone-utc' => '2021-04-27 19:09:20 UTC',
                'empty-array' => '[]',
                'array' => '{"array":{"array":[42]}}',
                'anonymous-object' => '{"class":"anonymous","class@anonymous::private":"private","protected":13,"public":[123,true]}',
                'test-object' => '{"class":"app\\components\\logger\\TestClass","public":"test","protected":123,"app\\components\\logger\\TestClass::private":[false]}',
                'exception' => '{"class":"Exception","message":"test-message","code":666,"trace":["\app\app\tests\unit\components\logger\FormatterTest.php:72","\app\vendor\phpunit\phpunit\src\Framework\TestCase.php:1153","\app\vendor\phpunit\phpunit\src\Framework\TestCase.php:842","\app\vendor\phpunit\phpunit\src\Framework\TestResult.php:687","\app\vendor\phpunit\phpunit\src\Framework\TestCase.php:796","\app\vendor\phpunit\phpunit\src\Framework\TestSuite.php:746","\app\vendor\phpunit\phpunit\src\Framework\TestSuite.php:746","\app\vendor\phpunit\phpunit\src\TextUI\TestRunner.php:641","\app\vendor\phpunit\phpunit\src\TextUI\Command.php:206","\app\vendor\phpunit\phpunit\src\TextUI\Command.php:162","\app\vendor\phpunit\phpunit\phpunit:61"],"previous":{"class":"app\components\logger\TestException","trace":["\app\app\tests\unit\components\logger\FormatterTest.php:75","\app\vendor\phpunit\phpunit\src\Framework\TestCase.php:1153","\app\vendor\phpunit\phpunit\src\Framework\TestCase.php:842","\app\vendor\phpunit\phpunit\src\Framework\TestResult.php:687","\app\vendor\phpunit\phpunit\src\Framework\TestCase.php:796","\app\vendor\phpunit\phpunit\src\Framework\TestSuite.php:746","\app\vendor\phpunit\phpunit\src\Framework\TestSuite.php:746","\app\vendor\phpunit\phpunit\src\TextUI\TestRunner.php:641","\app\vendor\phpunit\phpunit\src\TextUI\Command.php:206","\app\vendor\phpunit\phpunit\src\TextUI\Command.php:162","\app\vendor\phpunit\phpunit\phpunit:61"],"object":{"app\components\logger\TestException::property":"test-property"}}}',
                'function' => 'closure',
            ],
            (new Formatter([
                'text' => 'test',
                'null' => null,
                'true' => true,
                'false' => false,
                'int' => -13,
                'float' => 6.66,
                'big-float' => -1.23e45,
                'inf' => 1e100000,
                'string-123' => '123',
                'int-123' => 123,
                'float-123' => 123.0,
                'datetime-immutable' => new DateTimeImmutable('2021-04-27 19:09:20'),
                'datetime-with-timezone-boston' => new DateTime(
                    '2021-04-27 19:09:20',
                    new DateTimeZone('EST')
                ),
                'datetime-with-timezone-utc' => new DateTime(
                    '2021-04-27 19:09:20',
                    new DateTimeZone('UTC')
                ),
                'empty-array' => [],
                'array' => [
                    'array' => [
                        'array' => [42],
                    ],
                ],
                'anonymous-object' => new class {
                    /** @var string */
                    private $private = 'private';
                    /** @var int */
                    protected $protected = 13;
                    /** @var mixed[] */
                    public $public = [123, true];
                },
                'test-object' => new TestClass('test', 123, [false]),
                'exception' => new Exception(
                    'test-message',
                    666,
                    new TestException('test-property')
                ),
                'function' => static function (): string {
                    return 'test';
                },
            ]))->format(0)
        );
    }

    public function testRecursive(): void
    {
        self::assertArrayEquals(
            [
                'recursive-object' => static function (string $text): void {
                    self::assertStringMatchesFormat(
                        '{"class":"app\components\logger\TestRecursiveObject","self":"app\components\logger\TestRecursiveObject [recursion@%x]"}',
                        $text
                    );
                },
            ],
            (new Formatter([
                'recursive-object' => new TestRecursiveObject(),
            ]))->format()
        );
    }

    protected static function assertArrayEquals(array $expected, array $actual): void
    {
        self::compareArrays($expected, $actual, true);
    }

    protected static function assertArrayIncludes(array $expected, array $actual): void
    {
        self::compareArrays($expected, $actual, false);
    }

    private static function compareArrays(
        array $expected,
        array $actual,
        bool $completeEquality
    ): void {
        if ($completeEquality) {
            $expectedKeys = array_keys($expected);
            $actualKeys = array_keys($actual);
            sort($expectedKeys);
            sort($actualKeys);
            self::assertEquals($expectedKeys, $actualKeys);
        }

        foreach ($expected as $expectedKey => $expectedValue) {
            self::assertArrayHasKey($expectedKey, $actual);
            $actualValue = $actual[$expectedKey];
            if (is_array($expectedValue)) {
                self::assertIsArray($actualValue);
                self::compareArrays($expectedValue, $actualValue, $completeEquality);
            } elseif ($expectedValue instanceof Closure) {
                $expectedValue($actualValue);
            } else {
                self::assertEquals($expectedValue, $actualValue);
            }
        }
    }
}

class TestClass
{
    public $public;
    protected $protected;
    private $private;

    public function __construct($public, $protected, $private)
    {
        $this->public = $public;
        $this->protected = $protected;
        $this->private = $private;
    }
}

class TestRecursiveObject
{
    /** @var self */
    public $self;

    public function __construct()
    {
        $this->self = $this;
    }
}

class TestException extends \Exception
{
    private $property;

    public function __construct($property)
    {
        parent::__construct();
        $this->property = $property;
    }
}
