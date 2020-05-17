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
        $this->jsonBeforePath = $this->makeFilePath('before.json');
        $this->jsonAfterPath = $this->makeFilePath('after.json');
        $this->yamlBeforePath = $this->makeFilePath('before.yml');
        $this->yamlAfterPath = $this->makeFilePath('after.yml');
    }

    protected function makeFilePath($filename, $dir = "")
    {
        $path = self::FIXTURES_PATH . "/{$dir}{$filename}";

        if (file_exists($path)) {
            return $path;
        }
        throw new \Exception("[Test fixtures error] File '$path' not found");
    }

    public function testGenerateDiffPrettyFormat()
    {
        $expected = file_get_contents($this->makeFilePath('expectedPretty.txt', 'expected/'));
        
        $jsonDiff = generateDiff($this->jsonBeforePath, $this->jsonAfterPath);
        $yamlDiff = generateDiff($this->yamlBeforePath, $this->yamlAfterPath);

        $this->assertSame($expected, $jsonDiff);
        $this->assertSame($expected, $yamlDiff);
    }

    public function testGenerateDiffPlainFormat()
    {
        $expected = file_get_contents($this->makeFilePath('expectedPlain.txt', 'expected/'));
        
        $jsonDiff = generateDiff($this->jsonBeforePath, $this->jsonAfterPath, 'plain');
        $yamlDiff = generateDiff($this->yamlBeforePath, $this->yamlAfterPath, 'plain');

        $this->assertSame($expected, $jsonDiff);
        $this->assertSame($expected, $yamlDiff);
    }

    public function testGenerateDiffJsonFormat()
    {
        $expected = file_get_contents($this->makeFilePath('expectedJson.json', 'expected/'));
        
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
        $wrongExtFilePath = $this->makeFilePath('unknownExt.wrong');

        $this->expectExceptionMessage("[Parse error] Format 'wrong' is unknown for parsing");
        generateDiff($wrongExtFilePath, $this->jsonAfterPath);
    }

    public function testGenerateDiffUnknownFormat()
    {
        $unknownFormat = 'unknown';

        $this->expectExceptionMessage("[Render error] Format '$unknownFormat' is unknown for rendering");
        generateDiff($this->jsonBeforePath, $this->jsonAfterPath, $unknownFormat);
    }
}
