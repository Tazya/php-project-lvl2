<?php

namespace gendiff\Tests;

use PHPUnit\Framework\TestCase;

use function gendiff\differ\generateDiff;
use function gendiff\differ\makeAst;
use function gendiff\differ\parseFile;

class DiffTest extends TestCase
{
    protected $jsonConfigPath;
    protected $changedJsonConfigPath;
    protected $yamlConfigPath;
    protected $changedYamlConfigPath;

    protected $recursiveJsonConfigPath;
    protected $changedRecursiveJsonConfigPath;

    protected function setUp(): void
    {
        $this->jsonConfigPath = "tests/fixtures/flatBefore.json";
        $this->changedJsonConfigPath = "tests/fixtures/flatAfter.json";
        $this->yamlConfigPath = "tests/fixtures/flatBefore.yml";
        $this->changedYamlConfigPath = "tests/fixtures/flatAfter.yml";
        $this->recursiveJsonConfigPath = "tests/fixtures/recursiveBefore.json";
        $this->changedRecursiveJsonConfigPath = "tests/fixtures/recursiveAfter.json";
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

    public function testMakeAst()
    {
        $firstProperties = parseFile($this->recursiveJsonConfigPath);
        $secondProperties = parseFile($this->changedRecursiveJsonConfigPath);
        $expected = [
            [
                "name" => "common",
                "children" => [
                    [
                        "name" => "setting1",
                        "diff" => "same",
                        "value" => "Value 1"
                    ],
                    [
                        "name" => "setting2",
                        "diff" => "deleted",
                        "value" => "200"
                    ],
                    [
                        "name" => "setting3",
                        "diff" => "same",
                        "value" => true
                    ],
                    [
                        "name" => "setting6",
                        "diff" => "deleted",
                        "value" => ["key" => "value"]
                    ],
                    [
                        "name" => "setting4",
                        "diff" => "added",
                        "value" => "blah blah"

                    ],
                    [
                        "name" => "setting5",
                        "diff" => "added",
                        "value" => ["key5" => "value5"]
                    ]
                ]
            ],
            [
                "name" => "group1",
                "children" => [
                    [
                        "name" => "baz",
                        "diff" => "changed",
                        "value" => "bars",
                        "oldValue" => "bas"
                    ],
                    [
                        "name" => "foo",
                        "diff" => "same",
                        "value" => "bar"
                    ]
                ]
            ],
            [
                "name" => "group2",
                "diff" => "deleted",
                "value" => ["abc" => "12345"]
            ],
            [
                "name" => "group3",
                "diff" => "added",
                "value" => ["fee" => "100500"]
            ]
        ];
        
        $ast = makeAst($firstProperties, $secondProperties);
        $this->assertSame($expected, $ast);
    }
}
