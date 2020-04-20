<?php

namespace gendiff\Tests;

use PHPUnit\Framework\TestCase;

use function gendiff\differ\generateDiff;

class DiffTest extends TestCase
{
    protected $jsonConfigPath;
    protected $changedJsonConfigPath;
    protected $yamlConfigPath;
    protected $changedYamlConfigPath;

    protected function setUp(): void
    {
        $this->jsonConfigPath = "tests/fixtures/before.json";
        $this->changedJsonConfigPath = "tests/fixtures/after.json";
        $this->yamlConfigPath = "tests/fixtures/before.yml";
        $this->changedYamlConfigPath = "tests/fixtures/after.yml";
    }

    public function testGenerateDiffFileNotFound()
    {

        $path = "tests/fixtures/notExist.json";

        $this->expectExceptionMessage("File $path not found!\n");
        $diffJson = generateDiff($path, $this->jsonConfigPath);
    }

    public function testGenerateDiffWithoutChanges()
    {
        $expected = "host: hexlet.io\ntimeout: 50\nproxy: 123.234.53.22\n";

        $diffJson = generateDiff($this->jsonConfigPath, $this->jsonConfigPath);
        $diffYaml = generateDiff($this->yamlConfigPath, $this->yamlConfigPath);

        $this->assertSame($expected, $diffJson);
        $this->assertSame($expected, $diffYaml);
    }

    public function testGenerateDiffWithChanges()
    {
        $expected = "host: hexlet.io
+ timeout: 20
- timeout: 50
- proxy: 123.234.53.22
+ verbose: true\n";
        
        $diffJson = generateDiff($this->jsonConfigPath, $this->changedJsonConfigPath);
        $diffYaml = generateDiff($this->yamlConfigPath, $this->changedYamlConfigPath);

        $this->assertSame($expected, $diffJson);
        $this->assertSame($expected, $diffYaml);
    }
}
