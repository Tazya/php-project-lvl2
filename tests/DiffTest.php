<?php

namespace gendiff\Tests;

use PHPUnit\Framework\TestCase;

use function gendiff\differ\generateDiff;

class DiffTest extends TestCase
{
    protected $simpleConfigPath;
    protected $changedSimpleConfigPath;

    protected function setUp(): void
    {
        $this->simpleConfigPath = "tests/fixtures/simpleConfig.json";
        $this->changedSimpleConfigPath = "tests/fixtures/changedSimpleConfig.json";
    }

    public function testGenerateDiffFileNotFound()
    {

        $path = "tests/fixtures/notExist.json";
        $expected = "File $path cannot be found!\n";

        $diff = generateDiff($path, $this->simpleConfigPath);

        $this->assertSame($expected, $diff);
    }

    public function testGenerateDiffWithoutChanges()
    {
        $expected = "host: hexlet.io\ntimeout: 50\nproxy: 123.234.53.22\n";
        $diff = generateDiff($this->simpleConfigPath, $this->simpleConfigPath);

        $this->assertSame($expected, $diff);
    }

    public function testGenerateDiffWithChanges()
    {
        $expected = "host: hexlet.io
+ timeout: 20
- timeout: 50
- proxy: 123.234.53.22
+ verbose: true\n";
        $diff = generateDiff($this->simpleConfigPath, $this->changedSimpleConfigPath);

        $this->assertSame($expected, $diff);
    }
}
