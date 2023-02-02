<?php
/** @noinspection PhpPropertyOnlyWrittenInspection */
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
//            ['a "\'\\string', '"a \"\'\\\\string"' ],
//            [null,            'null'               ],
//            [true,            'true'               ],
//            [false,           'false'              ],
//            [-13,             '-13'                ],
//            [6.66,            '6.66'               ],
//            [-1.23e+45,       '-1.23E+45'          ],
//            [1e100000,        'INF'                ],
//            ['123',           '"123"'              ],
//            [123,             '123'                ],
//            [123.,            '123.0'              ],
//            [[],              '[]'                 ],
//            'datetime' => [
//                new DateTimeImmutable('2021-04-27 19:09:20'),
//                '2021-04-27 19:09:20 UTC',
//            ],
//            'datetime with timezone' => [
//                new DateTime('2021-04-27 19:09:20', new DateTimeZone('EST')),
//                '2021-04-27 19:09:20 EST',
//            ],
//            'function' => [
//                static function (): string {
//                    return 'test';
//                },
//                'closure',
//            ],
            'json' => [
                '{"1":1,"2":2,"three":3}', // ,"[4]":"[\"four\"]",5:[],6:{}
                <<<YAML
    JSON:
        1: 1
        2: 2
        "three": 3
YAML,
            ],
//            'text' => [
//                <<<TEXT
//Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
//TEXT,
//                <<<YAML
//    >
//        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut
//         labore et dolore magna aliqua.
//YAML,
//            ],
//            'list' => [
//                [
//                    'text',
//                    [],
//                    [1, 2, 3],
//                    ['a' => 1, 'b' => 2, 'c' => 3],
//                    new TestClass(1, '2', 3.),
//                ],
//                <<<YAML
//    - "text"
//    - []
//    -
//        - 1
//        - 2
//        - 3
//    -
//        "a": 1
//        "b": 2
//        "c": 3
//    - YamlFormatter\TestClass:
//        public: 1
//        protected: "2"
//        YamlFormatter\TestClass::private: 3.0
//YAML,
//            ],
//            'dict' => [
//                [
//                    'a' => 'text',
//                    'b' => [],
//                    'c' => [1, 2, 3],
//                    'd' => ['a' => 1, 'b' => 2, 'c' => 3],
//                    'e' => new TestClass(1, '2', 3.),
//                ],
//                <<<YAML
//    "a": "text"
//    "b": []
//    "c":
//        - 1
//        - 2
//        - 3
//    "d":
//        "a": 1
//        "b": 2
//        "c": 3
//    "e" YamlFormatter\TestClass:
//        public: 1
//        protected: "2"
//        YamlFormatter\TestClass::private: 3.0
//YAML,
//            ],
//            'exception' => [
//                new Exception(
//                    'test-message',
//                    666,
//                    new TestException('test-property')
//                ),
//                <<<YAML
//    exception: Exception
//    message: "test-message"
//    code: 666
//    trace:
//        - tests/FormatterTest.php:49
//        - YamlFormatter/FormatterTest::dataProvider
//        - vendor/phpunit/phpunit/src/Util/Annotation/DocBlock.php:426
//        - vendor/phpunit/phpunit/src/Util/Annotation/DocBlock.php:283
//        - vendor/phpunit/phpunit/src/Util/Test.php:322
//        - vendor/phpunit/phpunit/src/Framework/TestBuilder.php:74
//        - vendor/phpunit/phpunit/src/Framework/TestSuite.php:884
//        - vendor/phpunit/phpunit/src/Framework/TestSuite.php:236
//        - vendor/phpunit/phpunit/src/Framework/TestSuite.php:366
//        - vendor/phpunit/phpunit/src/Framework/TestSuite.php:505
//        - vendor/phpunit/phpunit/src/Framework/TestSuite.php:530
//        - vendor/phpunit/phpunit/src/TextUI/TestSuiteMapper.php:67
//        - vendor/phpunit/phpunit/src/TextUI/Command.php:390
//        - vendor/phpunit/phpunit/src/TextUI/Command.php:111
//        - vendor/phpunit/phpunit/src/TextUI/Command.php:96
//        - vendor/phpunit/phpunit/phpunit:98
//    previous:
//        exception: YamlFormatter\\TestException
//        trace:
//            - tests/FormatterTest.php:51
//            - YamlFormatter/FormatterTest::dataProvider
//            - vendor/phpunit/phpunit/src/Util/Annotation/DocBlock.php:426
//            - vendor/phpunit/phpunit/src/Util/Annotation/DocBlock.php:283
//            - vendor/phpunit/phpunit/src/Util/Test.php:322
//            - vendor/phpunit/phpunit/src/Framework/TestBuilder.php:74
//            - vendor/phpunit/phpunit/src/Framework/TestSuite.php:884
//            - vendor/phpunit/phpunit/src/Framework/TestSuite.php:236
//            - vendor/phpunit/phpunit/src/Framework/TestSuite.php:366
//            - vendor/phpunit/phpunit/src/Framework/TestSuite.php:505
//            - vendor/phpunit/phpunit/src/Framework/TestSuite.php:530
//            - vendor/phpunit/phpunit/src/TextUI/TestSuiteMapper.php:67
//            - vendor/phpunit/phpunit/src/TextUI/Command.php:390
//            - vendor/phpunit/phpunit/src/TextUI/Command.php:111
//            - vendor/phpunit/phpunit/src/TextUI/Command.php:96
//            - vendor/phpunit/phpunit/phpunit:98
//        YamlFormatter\TestException::property: "test-property"
//YAML
//            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function test($value, string $expectedFormatted): void
    {
        self::assertEquals($expectedFormatted, (new Formatter($value))->format(1)->asYaml());
    }

    /**
     * @throws Exception
     * @noinspection PhpUnusedPrivateFieldInspection
     */
    public function _test(): void
    {
        self::assertEquals(
            [
                'array' => '{"array":{"array":[42]}}',
                'anonymous-object' => '{"class":"anonymous","class@anonymous::private":"private","protected":13,"public":[123,true]}',
                'test-object' => '{"class":"app\\components\\logger\\TestClass","public":"test","protected":123,"app\\components\\logger\\TestClass::private":[false]}',
            ],
            (new Formatter([
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
            ]))->format(0)
        );
    }

//    public function testRecursive(): void
//    {
//        self::assertStringMatchesFormat(
//            <<<YAML
//YamlFormatter\TestRecursiveObject:
//                self: YamlFormatter\TestRecursiveObject [recursion@%x]
//YAML,
//            (new Formatter(new TestRecursiveObject()))->format(1)->asYaml()
//        );
//    }
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
