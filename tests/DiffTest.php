<?php

namespace gendiff\Tests;

use PHPUnit\Framework\TestCase;

use function gendiff\differ\generateDiff;

class DiffTest extends TestCase
{

    protected const FIXTURES_PATH = 'tests/fixtures';

    protected $jsonBeforePath;
    protected $jsonAfterPath;

    protected $yamlBeforePath;
    protected $yamlAfterPath;


    protected function setUp(): void
    {
        $this->jsonBeforePath = self::FIXTURES_PATH . "/before.json";
        $this->jsonAfterPath = self::FIXTURES_PATH . "\after.json";
        $this->yamlBeforePath = self::FIXTURES_PATH . "\before.yml";
        $this->yamlAfterPath = self::FIXTURES_PATH . "/after.yml";
    }

    public function testGenerateDiffPrettyFormat()
    {
        $expected = file_get_contents(self::FIXTURES_PATH . "/expected/expectedPretty.txt");
        
        $diffJson = generateDiff($this->jsonBeforePath, $this->jsonAfterPath);
        $diffYaml = generateDiff($this->yamlBeforePath, $this->yamlAfterPath);

        $this->assertSame($expected, $diffJson);
        $this->assertSame($expected, $diffYaml);
    }

    public function testGenerateDiffPlainFormat()
    {
        $expected = file_get_contents(self::FIXTURES_PATH . "/expected/expectedPlain.txt");
        
        $diffJson = generateDiff($this->jsonBeforePath, $this->jsonAfterPath, 'plain');
        $diffYaml = generateDiff($this->yamlBeforePath, $this->yamlAfterPath, 'plain');

        $this->assertSame($expected, $diffJson);
        $this->assertSame($expected, $diffYaml);
    }

    public function testGenerateDiffJsonFormat()
    {
        $expected = file_get_contents(self::FIXTURES_PATH . "/expected/expectedJson.json");
        
        $diffJson = generateDiff($this->jsonBeforePath, $this->jsonAfterPath, 'json');
        $diffYaml = generateDiff($this->yamlBeforePath, $this->yamlAfterPath, 'json');

        $this->assertSame($expected, $diffJson);
        $this->assertSame($expected, $diffYaml);
    }

    public function testGenerateDiffNotExisted()
    {
        $notExistedPath = self::FIXTURES_PATH . "/notExisted.json";

        $this->expectExceptionMessage("File '$notExistedPath' not found!\n");
        generateDiff($notExistedPath, $this->jsonAfterPath);
    }

    public function testGenerateDiffUnknownExt()
    {
        $wrongExtFile = self::FIXTURES_PATH . "/unknownExt.wrong";

        $this->expectExceptionMessage("File has an unknown extension: 'wrong'\n");
        generateDiff($wrongExtFile, $this->jsonAfterPath);
    }
}
