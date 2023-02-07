<?php
declare(strict_types=1);

namespace YamlFormatter;

use PHPUnit\Framework\TestCase;

class MyLogTest extends TestCase
{
    private const FILENAME = '../my_log.yml';

    protected function setUp(): void
    {
        parent::setUp();
        require(__DIR__ . '/../src/my_log.php');
    }

    protected function tearDown(): void
    {
//        unlink(self::FILENAME);
        parent::tearDown();
    }

    public function test(): void
    {
        my_log('test');
        my_log(new class{});
    }
}
