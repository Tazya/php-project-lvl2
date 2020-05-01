<?php

namespace gendiff\Tests;

use PHPUnit\Framework\TestCase;

use function gendiff\parsers\parseFile;

class ParsersTest extends TestCase
{
    public function testParseFile()
    {
        $jsonPath = "tests/fixtures/flatBefore.json";
        $yamlPath = "tests/fixtures/flatBefore.yml";

        $expected = [
            "host" => "hexlet.io",
            "timeout" => 50,
            "proxy" => "123.234.53.22"
        ];

        $this->assertSame($expected, parseFile($jsonPath));
        $this->assertSame($expected, parseFile($yamlPath));
    }

    public function testParseFileNotExisted()
    {
        $notExistedPath = "tests/fixtures/notExisted.json";

        $this->expectExceptionMessage("File '$notExistedPath' not found!\n");
        parseFile($notExistedPath);
    }

    public function testParseFileWrongExtension()
    {
        $wrongExtensionPath = "tests/fixtures/wrongExt.jsan";

        $this->expectExceptionMessage("File '$wrongExtensionPath' has a unknown extension: 'jsan'\n");
        parseFile($wrongExtensionPath);
    }
}
