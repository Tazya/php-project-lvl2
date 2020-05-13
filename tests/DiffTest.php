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
        $this->jsonAfterPath = self::FIXTURES_PATH . "/after.json";
        $this->yamlBeforePath = self::FIXTURES_PATH . "/before.yml";
        $this->yamlAfterPath = self::FIXTURES_PATH . "/after.yml";
    }

    public function testGenerateDiffPrettyFormat()
    {
        $expected = file_get_contents(self::FIXTURES_PATH . "/expected/expectedPretty.txt");
        
        $jsonDiff = generateDiff($this->jsonBeforePath, $this->jsonAfterPath);
        $yamlDiff = generateDiff($this->yamlBeforePath, $this->yamlAfterPath);

        $this->assertSame($expected, $jsonDiff);
        $this->assertSame($expected, $yamlDiff);
    }

    public function testGenerateDiffPlainFormat()
    {
        $expected = file_get_contents(self::FIXTURES_PATH . "/expected/expectedPlain.txt");
        
        $jsonDiff = generateDiff($this->jsonBeforePath, $this->jsonAfterPath, 'plain');
        $yamlDiff = generateDiff($this->yamlBeforePath, $this->yamlAfterPath, 'plain');

        $this->assertSame($expected, $jsonDiff);
        $this->assertSame($expected, $yamlDiff);
    }

    public function testGenerateDiffJsonFormat()
    {
        $expected = file_get_contents(self::FIXTURES_PATH . "/expected/expectedJson.json");
        
        $jsonDiff = generateDiff($this->jsonBeforePath, $this->jsonAfterPath, 'json');
        $yamlDiff = generateDiff($this->yamlBeforePath, $this->yamlAfterPath, 'json');

        $this->assertSame($expected, $jsonDiff);
        $this->assertSame($expected, $yamlDiff);
    }

    public function testGenerateDiffNotExisted()
    {
        $notExistedPath = self::FIXTURES_PATH . "/notExisted.json";

        $this->expectExceptionMessage("File '$notExistedPath' not found!");
        generateDiff($notExistedPath, $this->jsonAfterPath);
    }

    public function testGenerateDiffUnknownExt()
    {
        $wrongExtFile = self::FIXTURES_PATH . "/unknownExt.wrong";

        $this->expectExceptionMessage("File has an unknown extension: 'wrong'");
        generateDiff($wrongExtFile, $this->jsonAfterPath);
    }

    public function testGenerateDiffUnknownFormat()
    {
        $unknownFormat = 'unknown';

        $this->expectExceptionMessage("Unknown format '$unknownFormat'");
        generateDiff($this->jsonBeforePath, $this->jsonAfterPath, $unknownFormat);
    }
}
