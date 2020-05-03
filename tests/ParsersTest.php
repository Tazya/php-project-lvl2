<?php

namespace gendiff\Tests;

use PHPUnit\Framework\TestCase;

use function gendiff\parsers\parse;

class ParsersTest extends TestCase
{
    public function testParse()
    {
        $jsonPath = "tests/fixtures/flatBefore.json";
        $yamlPath = "tests/fixtures/flatBefore.yml";

        $contentJson = file_get_contents($jsonPath);
        $contentYaml = file_get_contents($yamlPath);

        $expected = [
            "host" => "hexlet.io",
            "timeout" => 50,
            "proxy" => "123.234.53.22"
        ];

        $this->assertSame($expected, parse('json', $contentJson));
        $this->assertSame($expected, parse('yml', $contentYaml));
    }

    public function testParseWrongExtension()
    {
        $wrongExtensionPath = "tests/fixtures/wrongExt.jsan";
        $content = file_get_contents($wrongExtensionPath);

        $this->expectExceptionMessage("File has a unknown extension: 'jsan'\n");
        parse('jsan', $content);
    }
}
