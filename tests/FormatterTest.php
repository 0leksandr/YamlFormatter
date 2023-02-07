<?php
/** @noinspection PhpMissingFieldTypeInspection */
/** @noinspection PhpPropertyOnlyWrittenInspection */
/** @noinspection PhpUnusedPrivateFieldInspection */
declare(strict_types=1);

namespace YamlFormatter;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use PHPUnit\Framework\TestCase;

class FormatterTest extends TestCase
{
    /**
     * @return array[]
     * @throws Exception
     */
    public function dataProvider(): array
    {
        return [
            ['a "\'\\string', '"a \"\'\\\\string"'],
            [null,            'null'              ],
            [true,            'true'              ],
            [false,           'false'             ],
            [-13,             '-13'               ],
            [6.66,            '6.66'              ],
            [-1.23e+45,       '-1.23E+45'         ],
            [1e100000,        'INF'               ],
            ['123',           '"123"'             ],
            [123,             '123'               ],
            [123.,            '123.0'             ],
            [[],              '[]'                ],
            'datetime' => [
                new DateTimeImmutable('2021-04-27 19:09:20'),
                '2021-04-27 19:09:20 UTC',
            ],
            'datetime with timezone' => [
                new DateTime('2021-04-27 19:09:20', new DateTimeZone('EST')),
                '2021-04-27 19:09:20 EST',
            ],
            'function' => [
                static function (): string {
                    return 'test';
                },
                'closure',
            ],
            'JSON list' => [
                '[1,"2",["three"]]',
                <<<YAML
JSON:
    - 1
    - "2"
    -
        - "three"
YAML,
            ],
            'JSON dict with numeric string keys' => [
                '{"0":0,"1":"1","2":"two"}',
                <<<YAML
JSON:
    - 0
    - "1"
    - "two"
YAML,
            ],
            'JSON dict with complex string keys' => [
                '{"1":1,"2":[2],"three":"3","[4]":"[\"four\"]","5":[],"6":{}}',
                <<<YAML
JSON:
    1: 1
    2:
        - 2
    "three": "3"
    "[4]":
        JSON:
            - "four"
    5: []
    6: []
YAML,
            ],
            'JSON dict with mixed keys' => [
                '{0:0,"1":"1"}',
                '"{0:0,\"1\":\"1\"}"',
            ],
            'long text' => [
                <<<TEXT
Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
TEXT,
                <<<YAML
>
    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore 
    et dolore magna aliqua.
YAML,
            ],
            'nested long text' => [
                [[[<<<TEXT
Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
TEXT]]],
                <<<YAML
-
    -
        - >
            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididun
            t ut labore et dolore magna aliqua.
YAML,
            ],
            'list' => [
                [
                    'text',
                    [],
                    [1, 2, 3],
                    ['a' => 1, 'b' => 2, 'c' => 3],
                    new TestClass(1, '2', 3.),
                ],
                <<<YAML
- "text"
- []
-
    - 1
    - 2
    - 3
-
    "a": 1
    "b": 2
    "c": 3
- YamlFormatter\TestClass:
    public: 1
    protected: "2"
    YamlFormatter\TestClass::private: 3.0
YAML,
            ],
            'dict' => [
                [
                    'a' => 'text',
                    'b' => [],
                    'c' => [1, 2, 3],
                    'd' => ['a' => 1, 'b' => 2, 'c' => 3],
                    'e' => new TestClass(1, '2', 3.),
                ],
                <<<YAML
"a": "text"
"b": []
"c":
    - 1
    - 2
    - 3
"d":
    "a": 1
    "b": 2
    "c": 3
"e":
    YamlFormatter\TestClass:
        public: 1
        protected: "2"
        YamlFormatter\TestClass::private: 3.0
YAML,
            ],
            'ND-array' => [
                [[[[42]]]],
                <<<YAML
-
    -
        -
            - 42
YAML,
            ],
            'object' => [
                new TestClass('test', 123, [false]),
                <<<YAML
YamlFormatter\TestClass:
    public: "test"
    protected: 123
    YamlFormatter\TestClass::private:
        - false
YAML,
            ],
            'anonymous object' => [
                new class {
                    private $private = 'private';
                    protected $protected = 13;
                    public $public = [123, true];
                },
                <<<YAML
anonymous:
    class@anonymous::private: "private"
    protected: 13
    public:
        - 123
        - true
YAML,
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function test($value, string $expectedFormatted): void
    {
        self::assertEquals($expectedFormatted, (new Formatter($value))->format()->asYaml());
    }

    public function testException(): void
    {
        $line1 = __LINE__ + 2;
        $line2 = __LINE__ + 4;
        $exception = new Exception(
            'test-message',
            666,
            new TestException('test-property')
        );
        /** @noinspection RegExpRepeatedSpace */
        $expected = <<<YAML
~^exception: Exception
message: "test-message"
code: 666
trace:
    - tests/FormatterTest.php:{$line1}
(    -     vendor/phpunit/phpunit/src/[\\w/]+\\.php:\\d+
)+    -     vendor/phpunit/phpunit/phpunit:\\d+
previous:
    exception: YamlFormatter\\\\TestException
    trace:
        - tests/FormatterTest.php:{$line2}
(        -     vendor/phpunit/phpunit/src/[\\w/]+\\.php:\\d+
)+        -     vendor/phpunit/phpunit/phpunit:\\d+
    YamlFormatter\\\\TestException::property: "test-property"$~m
YAML;
        self::assertMatchesRegularExpression($expected, (new Formatter($exception))->format()->asYaml());
    }

    public function testRecursive(): void
    {
        self::assertStringMatchesFormat(
            <<<YAML
YamlFormatter\TestRecursiveObject:
    self: YamlFormatter\TestRecursiveObject [recursion@%x]
YAML,
            (new Formatter(new TestRecursiveObject()))->format()->asYaml()
        );
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
        /** @noinspection UnusedConstructorDependenciesInspection */
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

class TestException extends Exception
{
    private $property;

    public function __construct($property)
    {
        parent::__construct();
        /** @noinspection UnusedConstructorDependenciesInspection */
        $this->property = $property;
    }
}
